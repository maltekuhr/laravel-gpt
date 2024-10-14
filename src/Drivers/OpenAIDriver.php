<?php

namespace MalteKuhr\LaravelGpt\Drivers;

use Closure;
use MalteKuhr\LaravelGpt\Contracts\Driver;
use MalteKuhr\LaravelGpt\Data\Message\ChatMessage;
use MalteKuhr\LaravelGpt\Data\Message\Parts\ChatFile;
use MalteKuhr\LaravelGpt\Data\Message\Parts\ChatFileUrl;
use MalteKuhr\LaravelGpt\Data\Message\Parts\ChatText;
use MalteKuhr\LaravelGpt\Data\Message\Parts\ChatFunctionCall;
use MalteKuhr\LaravelGpt\Enums\ChatRole;
use MalteKuhr\LaravelGpt\Enums\SchemaType;
use MalteKuhr\LaravelGpt\Exceptions\GptFunction\FunctionCallRequiresFunctionsException;
use MalteKuhr\LaravelGpt\Exceptions\GptFunction\MissingFunctionException;
use MalteKuhr\LaravelGpt\Facades\FunctionManager;
use MalteKuhr\LaravelGpt\Contracts\BaseChat;
use MalteKuhr\LaravelGpt\GptFunction;
use Illuminate\Support\Arr;
use OpenAI\Responses\StreamResponse;
use MalteKuhr\LaravelGpt\GPTChat;
use Exception;
use OpenAI;
use OpenAI\Client;
use MalteKuhr\LaravelGpt\Contracts\ChatMessagePart;

class OpenAIDriver implements Driver
{
    private Client $client;

    /**
     * Create a new OpenAIDriver instance.
     *
     * @param string $connection
     */
    public function __construct(
        private string $connection
    ) {
        $this->createClient();
    }

    /**
     * Create the OpenAI client.
     */
    protected function createClient(): void
    {
        $connection = config('laravel-gpt.connections.' . $this->connection);
        
        if ($connection['api'] === 'azure') {
            $this->client = OpenAI::factory()
                ->withBaseUri("{$connection['azure']['resource_name']}.openai.azure.com/openai/deployments/{$connection['azure']['deployment_id']}")
                ->withHttpHeader('api-key', $connection['azure']['api_key'])
                ->withQueryParam('api-version', $connection['azure']['api_version'])
                ->make();
        } else {
            $this->client = OpenAI::client($connection['openai']['api_key']);
        }
    }

    /**
     * Run the chat and return the updated BaseChat instance.
     *
     * @param BaseChat $chat
     * @param Closure|null $streamChat
     * @return void
     */
    public function run(BaseChat $chat, ?Closure $streamChat = null): void
    {
        $payload = $this->generatePayload($chat);
        
        $stream = $this->client->chat()->createStreamed($payload);

        $this->handleResponse($chat, $stream, $streamChat);   
    }

    /**
     * Generate the payload for the OpenAI API request.
     *
     * @param BaseChat $chat
     * @return array
     * @throws FunctionCallRequiresFunctionsException
     * @throws MissingFunctionException
     */
    protected function generatePayload(BaseChat $chat): array
    {
        $tools = $this->getTools($chat);

        $model = $chat->model();
        $version = config('laravel-gpt.models')[$model]['version'] ?? $model;

        return array_filter([
            'model' => $version,
            'messages' => $this->getMessages($chat),
            'temperature' => $chat->temperature(),
            'max_tokens' => $chat->maxTokens(),
            ...(count($tools) > 0 ? [
                'tools' => $tools,
                'tool_choice' => $this->getToolChoice($chat),
                'parallel_tool_calls' => $chat instanceof GptChat,
            ] : []),
        ], fn ($value) => $value !== null);
    }

    /**
     * Convert chat messages to OpenAI format.
     *
     * @param BaseChat $chat
     * @return array
     */
    protected function getMessages(BaseChat $chat): array
    {
        $messagesPayload = [];

        // add system message if it exists
        if ($chat->systemMessage() !== null) {
            $messagesPayload[] = [
                'role' => 'system',
                'content' => $chat->systemMessage(),
            ];
        }

        // add messages
        foreach ($chat->getMessages() as $index => $message) {
            if ($message->role === ChatRole::USER) {
                $messagesPayload[] = [
                    'role' => 'user',
                    'content' => Arr::map($message->parts, fn (ChatMessagePart $part) => match (get_class($part)) {
                        ChatText::class => [
                            'type' => 'text',
                            'text' => $part->text,
                        ],
                        ChatFile::class => [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => $part instanceof ChatFile ? $part->getFileUrl() : null
                            ]
                        ],
                        default => throw new Exception("The part type '".get_class($part)."' is not supported by the OpenAI driver."),
                    }),
                ];
            } else {
                $text = Arr::first($message->parts, fn (ChatMessagePart $part) => $part instanceof ChatText) ?? null;
                $functionCalls = array_filter($message->parts, fn (ChatMessagePart $part) => $part instanceof ChatFunctionCall);

                $messagesPayload[] = [
                    'role' => 'assistant',
                    'content' => $text?->text,
                    'tool_calls' => count($functionCalls) > 0 ? Arr::map($functionCalls, fn (ChatFunctionCall $functionCall) => [
                        'id' => $functionCall->id,
                        'type' => 'function',
                        'function' => [
                            'name' => $functionCall->name,
                            'strict' => true,
                            'arguments' => json_encode($functionCall->arguments),
                        ]
                    ]) : null,
                ];


                foreach ($functionCalls as $functionCall) {
                    if ($functionCall->response !== null) {
                        $messagesPayload[] = [
                            'tool_call_id' => $functionCall->id,
                            'role' => 'tool',
                            'content' => json_encode($functionCall->response),
                        ];
                    }
                }
            }
        }

        return $messagesPayload;
    }

    /**
     * Get the tools for the OpenAI API request.
     *
     * @param BaseChat $chat
     * @return array|null
     */
    protected function getTools(BaseChat $chat): ?array
    {
        if ($chat->functions() === null) {
            return null;
        }

        return array_map(function (GptFunction $function): array {
            return [
                'type' => 'function',
                'function' => FunctionManager::docs($function, SchemaType::JSON)
            ];
        }, $chat->functions());
    }

    /**
     * Get the tool choice for the OpenAI API request.
     *
     * @param BaseChat $chat
     * @return string|array|null
     * @throws MissingFunctionException
     * @throws FunctionCallRequiresFunctionsException
     */
    protected function getToolChoice(BaseChat $chat): string|array|null
    {
        if ($chat->functionCall() === null) {
            return 'auto';
        }

        if (is_array($chat->functionCall())) {
            throw new Exception("The function call is an array. This is not supported by the OpenAI driver.");
        }

        if (is_subclass_of($chat->functionCall(), GptFunction::class)) {
            $function = Arr::first(
                array: $chat->functions(),
                callback: fn (GptFunction $function) => $function instanceof ($chat->functionCall())
            );

            if ($function === null) {
                throw MissingFunctionException::create($chat->functionCall(), get_class($chat));
            }

            return [
                'type' => 'function',
                'function' => ['name' => $function->name()],
            ];
        }

        if ($chat->functionCall() && $chat->functions() === null) {
            throw FunctionCallRequiresFunctionsException::create();
        }

        return $chat->functionCall() ? 'required' : 'none';
    }

    /**
     * Handle the response from the OpenAI API.
     *
     * @param BaseChat $chat
     * @param StreamResponse $stream
     * @param Closure|null $streamChat
     * @return void
     */
    protected function handleResponse(BaseChat $chat, StreamResponse $stream, ?Closure $streamChat = null): void
    {
        $messages = $chat->getMessages();
        $message = new ChatMessage(
            role: ChatRole::ASSISTANT, 
            parts: []
        );
        
        $currentPart = null;
        $contentLastChangedAt = 0;
        $functionArgumentsBuffer = '';

        foreach ($stream as $response) {
            $delta = $response['choices'][0]['delta'];
            
            if (isset($delta['content'])) {
                if ($currentPart instanceof ChatText) {
                    $currentPart = new ChatText($currentPart->text . $delta['content']);
                } else {
                    if ($currentPart !== null) {
                        $message = $message->addPart($currentPart);
                    }
                    $currentPart = new ChatText($delta['content']);
                }
                
                // update chat message every 250 ms
                if (microtime(true) - $contentLastChangedAt >= 0.1 && $currentPart->text != '') {
                    $contentLastChangedAt = microtime(true);
                    $chat->addMessage($message->addPart($currentPart));
                    if ($streamChat !== null) {
                        $streamChat($chat);
                    }
                }
            }
            
            if (isset($delta['tool_calls'])) {
                foreach ($delta['tool_calls'] as $toolCall) {
                    $id = $toolCall['id'] ?? ($currentPart instanceof ChatFunctionCall ? $currentPart->id : null);
                    
                    // Check if ID has changed
                    if (!is_null($currentPart) && (!($currentPart instanceof ChatFunctionCall) || $id !== $currentPart->id)) {
                        $message = $message->addPart($currentPart);
                        $chat->addMessage($message);
                        
                        if ($streamChat !== null) {
                            $streamChat($chat);
                        }

                        $currentPart = null;
                        $functionArgumentsBuffer = '';
                    }
                    
                    if ($currentPart === null || !($currentPart instanceof ChatFunctionCall)) {
                        $functionArgumentsBuffer = $toolCall['function']['arguments'] ?? '';
                        $currentPart = new ChatFunctionCall(
                            id: $id,
                            name: $toolCall['function']['name'] ?? '',
                            arguments: []
                        );
                    } else {
                        $functionArgumentsBuffer .= $toolCall['function']['arguments'] ?? '';
                        $currentPart = new ChatFunctionCall(
                            id: $currentPart->id,
                            name: $toolCall['function']['name'] ?? $currentPart->name,
                            arguments: $currentPart->arguments
                        );
                    }

                    // Try to parse the JSON when it's complete
                    if ($this->isValidJson($functionArgumentsBuffer)) {
                        $parsedArguments = json_decode($functionArgumentsBuffer, true);
                        $currentPart = new ChatFunctionCall(
                            id: $currentPart->id,
                            name: $currentPart->name,
                            arguments: $parsedArguments
                        );
                        $functionArgumentsBuffer = '';
                    }
                }
            }
        }
        
        // Add the last part if it exists
        if ($currentPart !== null) {
            $message = $message->addPart($currentPart);
            $chat->addMessage($message);
            $streamChat($chat);
        }
    }

    /**
     * Check if a string is valid JSON.
     *
     * @param string $string
     * @return bool
     */
    private function isValidJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}