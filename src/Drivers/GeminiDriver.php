<?php

namespace MalteKuhr\LaravelGpt\Drivers;

use Closure;
use MalteKuhr\LaravelGpt\Data\Message\ChatMessage;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use MalteKuhr\LaravelGpt\Contracts\Driver;
use MalteKuhr\LaravelGpt\Contracts\BaseChat;
use MalteKuhr\LaravelGpt\Enums\ChatRole;
use MalteKuhr\LaravelGpt\Enums\SchemaType;
use MalteKuhr\LaravelGpt\GptFunction;
use MalteKuhr\LaravelGpt\Facades\FunctionManager;
use MalteKuhr\LaravelGpt\Data\Message\Parts\ChatFunctionCall;
use MalteKuhr\LaravelGpt\Data\Message\Parts\ChatText;
use MalteKuhr\LaravelGpt\Data\Message\Parts\ChatFile;
use MalteKuhr\LaravelGpt\Exceptions\GptFunction\MissingFunctionException;
use MalteKuhr\LaravelGpt\Exceptions\GptFunction\FunctionCallRequiresFunctionsException;
use Illuminate\Support\Arr;
use Exception;

class GeminiDriver implements Driver
{
    private $client;
    private string $connection;
    private string $apiKey;

    public function __construct(string $connection)
    {
        $this->connection = $connection;
        $config = config("laravel-gpt.connections.$connection");

        $this->apiKey = $config['api_key'];

        $this->client = HttpClient::create();
    }

    public function run(BaseChat $chat, ?Closure $streamChat = null): void
    {
        $model = $chat->model();

        $payload = $this->generatePayload($chat);

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

            $jsonBuffer = '';
            foreach ($this->client->stream($response) as $chunk) {
                $content = $chunk->getContent();

                $jsonBuffer .= $content;

                // Try to extract complete JSON objects
                while (preg_match('/(\[?\{(?:[^{}]|(?R))*\}\]?)/x', $jsonBuffer, $matches)) {
                    $jsonString = $matches[1];
                    $jsonData = json_decode(trim($jsonString, '[]'), true);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        if (isset($jsonData['candidates'][0])) {
                            $message = $this->handleCandidate($jsonData['candidates'][0], $originalMessage = $message, $chat, $streamChat);
                            if ($originalMessage !== $message) {
                                $chat->addMessage($message);
                                $streamChat($chat);
                            }
                        }
                        // Remove the processed JSON from the buffer
                        $jsonBuffer = substr($jsonBuffer, strlen($matches[0]));
                    } else {
                        // If we can't parse the JSON, break and wait for more data
                        break;
                    }
                }
            }
        } catch (TransportExceptionInterface $e) {
            throw new Exception("Gemini API request failed: " . $e->getMessage());
        }
    }

    protected function handleCandidate(array $candidate, ChatMessage $message, BaseChat $chat, ?Closure $streamChat = null): ChatMessage
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
     * @param BaseChat $chat
     * @return array
     */
    protected function generatePayload(BaseChat $chat): array
    {
        $tools = $this->getTools($chat);

        return [
            'systemInstruction' => [
                'parts' => [[
                    'text' => $chat->systemMessage(),
                ]]
            ],
            'contents' => $this->getContents($chat),
            'generationConfig' => [
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
     * @param BaseChat $chat
     * @return array
     */
    protected function getContents(BaseChat $chat): array
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
     * @param BaseChat $chat
     * @return array|null
     */
    protected function getTools(BaseChat $chat): ?array
    {
        if ($chat->functions() === null) {
            return null;
        }

            return [
                'functionDeclarations' => array_map(function (GptFunction $function): array {
                    return FunctionManager::docs($function, SchemaType::OPEN_API);
                }, $chat->functions())
            ];
    }


    /**
     * Get the tool configuration for the Gemini API request.
     *
     * @param BaseChat $chat
     * @return array|null
     * @throws MissingFunctionException
     * @throws FunctionCallRequiresFunctionsException
     */
    protected function getToolConfig(BaseChat $chat): ?array
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
                'allowedFunctionNames' => array_map(function (GptFunction $function) {
                    return $function->name();
                }, $chat->functions()),
            ];
        } else if (is_string($chat->functionCall())) {
            $function = array_filter($chat->functions(), fn (GptFunction $function) => get_class($function) === $chat->functionCall())[0] ?? null;

            if ($function === null) {
                throw MissingFunctionException::create($chat->functionCall(), get_class($chat));
            }
            
            $config = [
                'mode' => 'ANY',
            ];
        } else if (is_array($chat->functionCall())) {
            $allowedFunctionNames = array_map(function ($functionClass) use ($chat) {
                $function = Arr::first($chat->functions(), fn (GptFunction $f) => get_class($f) === $functionClass);
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