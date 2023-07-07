<?php

namespace MalteKuhr\LaravelGPT;

use Closure;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\FunctionCallRequiresFunctionsException;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\MissingFunctionException;
use MalteKuhr\LaravelGPT\Extensions\FillableGPTChat;
use MalteKuhr\LaravelGPT\Extensions\FillableGPTFunction;
use MalteKuhr\LaravelGPT\Helper\Dir;
use MalteKuhr\LaravelGPT\Managers\FunctionManager;
use MalteKuhr\LaravelGPT\Models\ChatMessage;

abstract class GPTAction
{
    use Dir;

    /**
     * @var FillableGPTChat|null
     */
    protected ?FillableGPTChat $chat = null;

    /**
     * Create a new GPTAction instance.
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
     * Specifies the function to be invoked by the model. The function is implemented as a
     * Closure which may take parameters that are provided by the model. If extra arguments
     * are included in the documentation to optimize model's performance (by allowing it more
     * thinking time), these can be disregarded by not including them within the Closure
     * parameters.
     *
     * @return Closure
     */
    abstract public function function(): Closure;

    /**
     * @return string
     */
    public function functionName(): string
    {
        return FunctionManager::getFunctionName($this, ['GPT', 'Action']);
    }

    /**
     * The description of what the function does. This is utilized for generating the
     * function documentation.
     *
     * @return string
     */
    public function description(): string
    {
        return 'The function you need to call.';
    }

    /**
     * Defines the rules for input validation and JSON schema generation. Override this
     * method to provide custom validation rules for the function. The documentation will
     * have the same order as the rules are defined in this method.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
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
     * Sends the chat to the OpenAI API and returns the result of the function call.
     *
     * @param string $message
     * @return ChatMessage
     * @throws FunctionCallRequiresFunctionsException
     * @throws MissingFunctionException
     */
    public function send(string $message): mixed
    {
        $this->chat = FillableGPTChat::make(
            systemMessage: fn () => $this->systemMessage(),
            functions: fn () => [
                new FillableGPTFunction(
                    name: fn () => $this->functionName(),
                    description: fn () => $this->description(),
                    function: fn () => $this->function(),
                    rules: fn () => $this->rules(),
                )
            ],
            functionCall: fn () => FillableGPTFunction::class,
            model: fn () => $this->model(),
            temperature: fn () => $this->temperature(),
            maxTokens: fn () => $this->maxTokens(),
            sending: fn () => $this->sending(),
            received: fn () => $this->received(),
        );

        $this->chat->addMessage($message);
        $this->chat->send();

        return $this->chat->latestMessage()->content;
    }
}