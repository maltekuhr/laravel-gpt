<?php

namespace MalteKuhr\LaravelGPT\Generators;

use Illuminate\Support\Arr;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\FunctionCallRequiresFunctionsException;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\MissingFunctionException;
use MalteKuhr\LaravelGPT\GPTChat;
use MalteKuhr\LaravelGPT\GPTFunction;
use MalteKuhr\LaravelGPT\Managers\FunctionManager;
use MalteKuhr\LaravelGPT\Models\ChatMessage;

class ChatPayloadGenerator
{
    /**
     * @param GPTChat $chat
     */
    protected function __construct(
        protected GPTChat $chat
    ) {}

    /**
     * Creates a new instance of the class.
     *
     * @param GPTChat $chat
     * @return self
     */
    public static function make(GPTChat $chat): self
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
            'functions' => self::getFunctions(),
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
    protected function getFunctions(): ?array
    {
        // handle if function call is null or []
        if ($this->chat->functions() == null) {
            return null;
        }

        // get functions
        $functions = $this->chat->functions();

        // remove unused functions when function call
        if (is_string($this->chat->functionCall())) {
            $functions = Arr::where($functions, function (GPTFunction $function) {
                return $function instanceof ($this->chat->functionCall());
            });
        }

        // generate docs for functions
        return array_map(function (GPTFunction $function): array {
            return FunctionManager::make($function)->docs();
        }, $functions);
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

        if (is_subclass_of($this->chat->functionCall(), GPTFunction::class)) {
            /* @var GPTFunction $function */
            $function = Arr::first(
                array: $this->chat->functions(),
                callback: fn (GPTFunction $function) => $function instanceof ($this->chat->functionCall())
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