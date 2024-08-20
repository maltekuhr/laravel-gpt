<?php

namespace MalteKuhr\LaravelGPT\Drivers;

use Closure;
use MalteKuhr\LaravelGPT\Data\Message\ChatMessage;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;
use MalteKuhr\LaravelGPT\Contracts\Driver;
use MalteKuhr\LaravelGPT\GPTChat;
use MalteKuhr\LaravelGPT\Enums\ChatRole;
use MalteKuhr\LaravelGPT\Enums\SchemaType;
use MalteKuhr\LaravelGPT\GPTFunction;
use MalteKuhr\LaravelGPT\Facades\FunctionManager;
use MalteKuhr\LaravelGPT\Data\Message\Parts\ChatFunctionCall;
use MalteKuhr\LaravelGPT\Data\Message\Parts\ChatText;
use MalteKuhr\LaravelGPT\Data\Message\Parts\ChatFile;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\MissingFunctionException;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\FunctionCallRequiresFunctionsException;
use Illuminate\Support\Arr;
use Exception;

class GeminiDriver implements Driver
{
    private $client;
    private string $connection;
    private string $location;
    private string $projectId;
    private string $apiKey;

    public function __construct(string $connection)
    {
        $this->connection = $connection;
        $config = config("laravel-gpt.connections.$connection");

        $this->apiKey = $config['api_key'];

        $this->client = HttpClient::create();
    }

    public function run(GPTChat $chat, ?Closure $streamChat = null): void
    {
        $model = $chat->model();

        $payload = $this->generatePayload($chat);

        dump($payload);

        $messages = $chat->getMessages();
        $message = new ChatMessage(
            role: ChatRole::ASSISTANT, 
            parts: []
        );

        try {
            $response = $this->client->request('POST', "https://generativelanguage.googleapis.com/v1beta/models/{$model}:streamGenerateContent", [
                'headers' => [
                    'x-goog-api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
                'buffer' => false,
            ]);


            if ($response->getStatusCode() !== 200) {
                $error = json_decode($response->getContent(false))[0]?->error ?? null;
                throw new Exception("Gemini API request failed ({$response->getStatusCode()}): " . json_encode($error));
            }

            foreach ($this->client->stream($response) as $chunk) {
                $content = $chunk->getContent();

                if (preg_match('/\{(?:[^{}]|(?R))*\}/x', $content, $matches)) {
                    $jsonData = json_decode($matches[0], true);
                    if (json_last_error() === JSON_ERROR_NONE && isset($jsonData['candidates'][0])) {
                        $message = $this->handleCandidate($jsonData['candidates'][0], $originalMessage = $message, $chat, $streamChat);
                        if ($originalMessage !== $message) {
                            $chat = $chat->setMessages([...$messages, $message]);
                            $streamChat($chat);
                        }
                    }
                }
            }
        } catch (TransportExceptionInterface $e) {
            throw new Exception("Gemini API request failed: " . $e->getMessage());
        }
    }

    protected function handleCandidate(array $candidate, ChatMessage $message, GPTChat $chat, ?Closure $streamChat = null): ChatMessage
    {
        foreach($candidate['content']['parts'] as $part) {

            if (isset($part['functionCall'])) {
                $message = $message->addPart(new ChatFunctionCall(
                    id: null,
                    name: $part['functionCall']['name'],
                    arguments: $part['functionCall']['args']
                ));
            } else if (isset($part['text'])) {
                // get last part if it is a text part
                $lastPart = Arr::last($message->parts);

                if ($lastPart instanceof ChatText) {
                    $message = $message->replacePart(count($message->parts) - 1, new ChatText(
                        text: $lastPart->text . $part['text']
                    ));
                } else {
                    $message = $message->addPart(new ChatText(
                        text: $part['text']
                    ));
                }
            }
        }

        return $message;
    }

    /**
     * Generate the payload for the Gemini API request.
     *
     * @param GPTChat $chat
     * @return array
     */
    protected function generatePayload(GPTChat $chat): array
    {
        $tools = $this->getTools($chat);

        return [
            'systemInstruction' => [
                'parts' => [[
                    'text' => $chat->systemMessage(),
                ]]
            ],
            'contents' => $this->getContents($chat),
            'generation_config' => [
                'temperature' => $chat->temperature(),
                'maxOutputTokens' => $chat->maxTokens(),
            ],
            ...(count($tools ?? []) > 0 ? [
                'tools' => $tools,
                'toolConfig' => $this->getToolConfig($chat),
            ] : []),
        ];
    }

    /**
     * Convert chat messages to Gemini format.
     *
     * @param GPTChat $chat
     * @return array
     */
    protected function getContents(GPTChat $chat): array
    {
        $contents = [];

        // add messages
        foreach ($chat->getMessages() as $message) {
            $parts = [];
            foreach ($message->parts as $part) {
                $parts[] = match (get_class($part)) {
                    ChatText::class => [
                        'text' => $part->text
                    ],
                    ChatFile::class => [
                        'inlineData' => [
                            'mimeType' => $part->mimeType,
                            'data' => $part->content
                        ]
                    ],
                    ChatFunctionCall::class => [
                        'functionCall' => [
                            'name' => $part->name,
                            'args' => $part->arguments,
                        ]
                    ],
                    default => throw new Exception("The part type '" . get_class($part) . "' is not supported by the Gemini driver."),
                };
            }
            
            $contents[] = [
                'role' => $message->role === ChatRole::USER ? 'user' : 'model',
                'parts' => $parts,
            ];

            // Add function call responses as separate user messages
            $functionCalls = array_filter($message->parts, fn ($part) => $part instanceof ChatFunctionCall && $part->response !== null);
            if ($message->role === ChatRole::ASSISTANT && count($functionCalls) > 0) {
                $contents[] = [
                    'role' => 'user',
                    'parts' => array_values(array_map(fn (ChatFunctionCall $functionCall) => [
                        'functionResponse' => [
                            'name' => $functionCall->name,
                            'response' => $functionCall->response,
                        ]
                    ], $functionCalls)),
                ];
            }
        }

        return $contents;
    }

    /**
     * Get the tools for the Gemini API request.
     *
     * @param GPTChat $chat
     * @return array|null
     */
    protected function getTools(GPTChat $chat): ?array
    {
        if ($chat->functions() === null) {
            return null;
        }

            return [
                'functionDeclarations' => array_map(function (GPTFunction $function): array {
                    return FunctionManager::docs($function, SchemaType::OPEN_API);
                }, $chat->functions())
            ];
    }


    /**
     * Get the tool configuration for the Gemini API request.
     *
     * @param GPTChat $chat
     * @return array|null
     * @throws MissingFunctionException
     * @throws FunctionCallRequiresFunctionsException
     */
    protected function getToolConfig(GPTChat $chat): ?array
    {
        if ($chat->functions() === null && $chat->functionCall() !== null && $chat->functionCall() !== false) {
            throw FunctionCallRequiresFunctionsException::create();

        }

        if ($chat->functionCall() === false) {
            $config = [
                'mode' => 'NONE'
            ];
        } else if ($chat->functionCall() === null) {
            $config = [
                'mode' => 'AUTO',
            ];
        } else if ($chat->functionCall() === true) {
            $config = [
                'mode' => 'ANY',
            ];
        } else if (is_string($chat->functionCall())) {
            $function = array_filter($chat->functions(), fn (GPTFunction $function) => get_class($function) === $chat->functionCall())[0] ?? null;

            if ($function === null) {
                throw MissingFunctionException::create($chat->functionCall(), get_class($chat));
            }
            
            $config = [
                'mode' => 'ANY',
                'allowedFunctionNames' => [
                    $function->name(),
                ]
            ];
        } else if (is_array($chat->functionCall())) {
            $allowedFunctionNames = array_map(function ($functionClass) use ($chat) {
                $function = Arr::first($chat->functions(), fn (GPTFunction $f) => get_class($f) === $functionClass);
                if ($function === null) {
                    throw MissingFunctionException::create($functionClass, get_class($chat));
                }
                return $function->name();
            }, $chat->functionCall());

            $config = [
                'mode' => 'ANY',
                'allowedFunctionNames' => $allowedFunctionNames,
            ];
        }

        return [
            'functionCallingConfig' => $config
        ];
    }
}