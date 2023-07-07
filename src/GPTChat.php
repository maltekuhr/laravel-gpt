<?php

namespace MalteKuhr\LaravelGPT;

use MalteKuhr\LaravelGPT\Concerns\HasChat;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\FunctionCallRequiresFunctionsException;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\MissingFunctionException;
use MalteKuhr\LaravelGPT\Helper\Dir;
use MalteKuhr\LaravelGPT\Managers\ChatManager;

class GPTChat
{
    use HasChat;
    use Dir;

    /**
     * Create a new GPTChat instance.
     *
     * @param ...$arguments
     * @return static
     */
    public static function make(...$arguments): static
    {
        return new static(...$arguments);
    }

    /**
     * The message which explains the assistant what to do and which rules to follow.
     *
     * @return string|null
     */
    public function systemMessage(): ?string
    {
        return null;
    }

    /**
     * The functions which are available to the assistant. The functions must be
     * an array of classes (e.g. [new SaveSentimentGPTFunction()]). The functions
     * must extend the GPTFunction class.
     *
     * @return array|null
     */
    public function functions(): ?array
    {
        return null;
    }

    /**
     * The function call method can force the model to call a specific function or
     * force the model to answer with a message. If you return with the class name
     * e.g. SaveSentimentGPTFunction::class the model will call the function. If
     * you return with false the model will answer with a message. If you return
     * with null or true the model will decide if it should call a function or
     * answer with a message.
     *
     * @return string|bool|null
     */
    public function functionCall(): string|bool|null
    {
        return null;
    }

    /**
     * The model version which should be used for the request. Make sure that the
     * model is available for the Chat Completion API (e.g. gpt-3.5-turbo, gpt-4).
     * The code will not automatically switch on to a version with a higher token
     * limit as it can't predict the needed tokens. Make sure that the model has
     * enough tokens to complete the request. If needed you can switch the model
     * here dynamically. This method will be called before every request against
     * the OpenAI API.
     *
     * @return string
     */
    public function model(): string
    {
        return config('laravel-gpt.default_model');
    }

    /**
     * The temperature of the response. A higher temperature will result in more
     * random responses. Must be between 0 and 2.
     *
     * @return ?float
     */
    public function temperature(): ?float
    {
        return null;
    }

    /**
     * Retrieves the maximum token limit per request during a chat conversation. This
     * limit is not for the entire conversation, but for each individual request. For
     * example, if the limit is 100, and a function call uses 50 tokens, the final
     * response will still have 100 tokens available.
     *
     * @return int|null
     */
    public function maxTokens(): ?int
    {
        return null;
    }


    /**
     * The hook method which will be called before the request is sent to the
     * model. This hook is also called before sending a function call. If you
     * want to remove the latest message it isn't enough to just return false
     * the message needs to be removed from $this->messages manually if you
     * want to remove it. If you return false the chat will be paused and if
     * you return true the request will be sent to the model.
     *
     * @return bool
     */
    public function sending(): bool
    {
        return true;
    }

    /**
     * The hook method which will be called after the response was received and
     * the answer was added to the chat. If you return false the chat will be
     * paused and if you return true the answer will be handled (e.g. function
     * call executed and returned to the model). You can access the messages
     * using $this->messages.
     *
     * @return bool
     */
    public function received(): bool
    {
        return true;
    }

    /**
     * Sends the chat to the OpenAI API and returns the updated instance of the
     * object including the new messages in the chat.
     *
     * @return $this
     * @throws FunctionCallRequiresFunctionsException
     * @throws MissingFunctionException
     */
    public function send(): self
    {
        return ChatManager::make($this)->send();
    }
}