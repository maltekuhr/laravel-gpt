<?php

namespace MalteKuhr\LaravelGpt;
use Closure;
use MalteKuhr\LaravelGpt\Facades\FunctionManager;
use MalteKuhr\LaravelGpt\Contracts\BaseChat;
use MalteKuhr\LaravelGpt\Facades\ChatManager;
use MalteKuhr\LaravelGpt\Contracts\ChatMessagePart;
use MalteKuhr\LaravelGpt\Enums\ChatRole;
use MalteKuhr\LaravelGpt\Data\Message\Parts\ChatFunctionCall;
use RuntimeException;

abstract class GptAction extends BaseChat
{
    /**
     * The function to be invoked by the model.
     *
     * @return Closure
     */
    abstract public function function(): Closure;

    /**
     * The name of the function to be invoked by the model.
     *
     * @return string
     */
    public function functionName(): string
    {
        return FunctionManager::getFunctionName($this, ['Gpt', 'Action']);
    }

    /**
     * Get the description of what the function does.
     *
     * @return string
     */
    public function description(): string
    {
        return 'The function you need to call.';
    }

    /**
     * Get the rules for the function.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Define the functions available for this action.
     *
     * @return array
     */
    public function functions(): array
    {
        return [
            new class($this) extends GptFunction {
                public function __construct(
                    private GptAction $action
                ) {}

                public function name(): string
                {
                    return $this->action->functionName();
                }

                public function description(): string
                {
                    return $this->action->description();
                }

                public function function(): Closure
                {
                    return $this->action->function();
                }

                public function rules(): array
                {
                    return $this->action->rules();
                }
            }
        ];
    }

    /**
     * Require the model to call a function.
     *
     * @return bool
     */
    public function functionCall(): bool
    {
        return true;
    }

    /**
     * Run the chat and get the response.
     *
     * @throws RuntimeException
     * @return array
     */
    public function run(bool $sync = true): array
    {
        $latestMessage = $this->getLatestMessage();

        if (!$latestMessage || $latestMessage->role !== ChatRole::USER) {
            throw new RuntimeException('The latest message must be from the user before running the chat.');
        }

        ChatManager::send($this, sync: $sync);

        $response = $this->getLatestMessage();

        /* @var ChatFunctionCall $functionCall */
        $functionCall = array_filter($response->parts, fn (ChatMessagePart $part) => $part instanceof ChatFunctionCall)[0];

        return $functionCall->response;
    }
}