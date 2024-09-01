<?php

namespace MalteKuhr\LaravelGpt\Generators;

use Illuminate\Support\Arr;
use MalteKuhr\LaravelGpt\Exceptions\GptFunction\FunctionCallRequiresFunctionsException;
use MalteKuhr\LaravelGpt\Exceptions\GptFunction\MissingFunctionException;
use MalteKuhr\LaravelGpt\GptChat;
use MalteKuhr\LaravelGpt\GptFunction;
use MalteKuhr\LaravelGpt\Managers\FunctionManager;
use MalteKuhr\LaravelGpt\Models\ChatMessage;

class ChatPayloadGenerator
{
    /**
     * @param GptChat $chat
     */
    protected function __construct(
        protected GptChat $chat
    ) {}

    /**
     * Creates a new instance of the class.
     *
     * @param GptChat $chat
     * @return self
     */
    public static function make(GptChat $chat): self
    {
        return new self($chat);
    }

    /**
     * Generates the payload for the chat completion endpoint.
     *
     * @return array
     * @throws FunctionCallRequiresFunctionsException
     * @throws MissingFunctionException
     */
    public function generate(): array
    {
        return array_filter([
            'model' => $this->chat->model(),
            'messages' => self::getMessages(),
            'tools' => self::getTools(),
            'function_call' => self::getFunctionCall(),
            'temperature' => $this->chat->temperature(),
            'max_tokens' => $this->chat->maxTokens(),
        ], fn ($value) => $value !== null);
    }

    /**
     * Converts the chat messages into the required format.
     *
     * @return array
     */
    protected function getMessages(): array
    {
        $messages = array_map(function (ChatMessage $message) {
            return $message->toArray();
        }, $this->chat->messages);

        if ($this->chat->systemMessage()) {
            array_unshift($messages, [
                'role' => 'system',
                'content' => $this->chat->systemMessage()
            ]);
        }

        return $messages;
    }

    /**
     * Gets the functions required for the request. Removes
     * unused functions when function call is set.
     *
     * @return array|null
     */
    protected function getTools(): ?array
    {
        // handle if function call is null or []
        if ($this->chat->functions() == null) {
            return null;
        }

        // get functions
        $functions = $this->chat->functions();

        // remove unused functions when function call
        if (is_string($this->chat->functionCall())) {
            $functions = Arr::where($functions, function (GptFunction $function) {
                return $function instanceof ($this->chat->functionCall());
            });
        }

        // generate docs for functions
        $functions = array_map(function (GptFunction $function): array {
            return [
                'type' => 'function',
                'function' => FunctionManager::make($function)->docs()
            ];
        }, $functions);

        // return tools
        return [
            ...$functions
        ];
    }

    /**
     * Converts the functionCall() into the required format.
     *
     * @return string|array|null
     * @throws FunctionCallRequiresFunctionsException
     * @throws MissingFunctionException
     */
    protected function getFunctionCall(): string|array|null
    {
        // handle if function call is null
        if ($this->chat->functionCall() === null) {
            return null;
        }

        if (is_subclass_of($this->chat->functionCall(), GptFunction::class)) {
            /* @var GptFunction $function */
            $function = Arr::first(
                array: $this->chat->functions(),
                callback: fn (GptFunction $function) => $function instanceof ($this->chat->functionCall())
            );

            // handle if function call is not in functions
            if ($function == null) {
                throw MissingFunctionException::create($this->chat->functionCall(), get_class($this->chat));
            }

            return [
                'name' => $function->name(),
            ];
        }

        if ($this->chat->functionCall() && $this->chat->functions() == null) {
            throw FunctionCallRequiresFunctionsException::create();
        }

        return $this->chat->functionCall() ? 'auto' : 'none';
    }
}