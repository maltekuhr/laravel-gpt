<?php

namespace MalteKuhr\LaravelGPT\Managers;

use Illuminate\Support\Arr;
use MalteKuhr\LaravelGPT\Enums\ChatRole;
use MalteKuhr\LaravelGPT\Exceptions\GPTChat\ErrorPatternFoundException;
use MalteKuhr\LaravelGPT\Exceptions\GPTChat\NoFunctionCallException;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\FunctionCallDecodingException;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\FunctionCallRequiresFunctionsException;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\MissingFunctionException;
use MalteKuhr\LaravelGPT\Facades\OpenAI;
use MalteKuhr\LaravelGPT\Generators\ChatPayloadGenerator;
use MalteKuhr\LaravelGPT\GPTChat;
use MalteKuhr\LaravelGPT\GPTFunction;
use MalteKuhr\LaravelGPT\Models\ChatMessage;
use OpenAI\Exceptions\TransporterException;
use OpenAI\Responses\Chat\CreateResponseMessage;

class ChatManager
{
    /**
     * @param GPTChat $chat
     */
    protected function __construct(
        protected GPTChat $chat
    ) {}

    /**
     * @param GPTChat $chat
     * @return self
     */
    public static function make(GPTChat $chat): self
    {
        return new self($chat);
    }

    /**
     * Sends the current conversation against the OpenAI Chat Completion API.
     *
     * @return GPTChat
     * @throws FunctionCallRequiresFunctionsException
     * @throws MissingFunctionException
     * @throws TransporterException
     * @throws ErrorPatternFoundException
     */
    public function send(): GPTChat
    {
        // listen sending hook
        if (!$this->chat->sending()) {
            return $this->chat;
        }

        // send chat completion request
        $answer = OpenAI::chat()->create(
            $payload = ChatPayloadGenerator::make($this->chat)->generate()
        )->choices[0]->message;

        // handle the response
        self::handleResponse($answer);

        // listen received hook
        if (!$this->chat->received()) {
            return $this->chat;
        }

        // check if function call is different then expected
        if (isset($payload['function_call']) && $answer->functionCall?->name !== $payload['function_call']['name'] && !in_array($payload['function_call']['name'], ['auto', 'none'])) {
            // handle wrong or missing function call
            return $this->handleWrongFunctionCall($payload);
        }

        // handle next steps
        $latestMessage = $this->chat->latestMessage();
        if ($latestMessage->role == ChatRole::ASSISTANT && $latestMessage->functionCall != null) {
            return self::handleFunctionCall($latestMessage);
        } else {
            return $this->chat;
        }
    }

    protected function handleWrongFunctionCall(array $payload): GPTChat
    {
        // throw exception if model hasn't changed answer after feedback
        if (Arr::first($this->chat->messages, fn (ChatMessage $message) => $message->role == ChatRole::FUNCTION && $message->content == NoFunctionCallException::modelMessage())) {
            throw NoFunctionCallException::create();
        }

        // provide feedback to model
        $this->chat->addMessage(
            ChatMessage::from(
                role: ChatRole::FUNCTION,
                content: NoFunctionCallException::modelMessage(),
                name: $payload['function_call']['name']
            )
        );

        return $this->send();
    }

    /**
     * Handles the response from the OpenAI Chat Completion API.
     *
     * @param CreateResponseMessage $answer
     * @return void
     */
    protected function handleResponse(CreateResponseMessage $answer): void
    {
        try {
            $this->chat->addMessage(
                ChatMessage::fromResponseMessage($answer)
            );
        } catch (FunctionCallDecodingException $exception) {
            $this->chat->addMessage(
                ChatMessage::from(
                    role: ChatRole::ASSISTANT,
                    content: json_encode($answer->functionCall)
                )
            );

            $this->chat->addMessage(
                ChatMessage::from(
                    role: ChatRole::FUNCTION,
                    content: [
                        'errors' => $exception->getMessage()
                    ],
                    name: $answer->functionCall->name
                )
            );
        }
    }

    /**
     * Executes the function call from the OpenAI Chat Completion API and
     * adds the result to the conversation. If possible, the conversation
     * will be continued.
     *
     * @param ChatMessage $answer
     * @return GPTChat
     * @throws ErrorPatternFoundException
     * @throws TransporterException
     * @throws MissingFunctionException
     * @throws FunctionCallRequiresFunctionsException
     */
    protected function handleFunctionCall(ChatMessage $answer): GPTChat
    {
        // get the object of the function
        $function = Arr::first(
            array: $this->chat->functions(),
            callback: fn (GPTFunction $function) => $function->name() == $answer->functionCall->name
        );

        // make sure that the function exists
        if ($function == null) {
            $this->chat->addMessage(
                ChatMessage::from(
                    role: ChatRole::FUNCTION,
                    content: [
                        'errors' => 'Function not found.'
                    ],
                    name: $answer->functionCall->name
                )
            );

            return $this->chat;
        }

        // call function and add result to conversation
        $this->chat->addMessage(
            FunctionManager::make($function)->call($answer->functionCall->arguments)
        );

        // check if function returned two times the same validation errors
        self::noErrorPatternExits();

        // make sure that the next request isn't the same
        $isForced = get_class($function) == $this->chat->functionCall();

        // check if function has response
        $hasResponse = $this->chat->latestMessage()->content !== null;

        // check if function response has errors
        $hasErrors = isset($this->chat->latestMessage()->content['errors']);

        // check if function call
        if ((!$isForced && $hasResponse) || $hasErrors) {
            // proceed in conversation with model
            return self::send();
        } else {
            return $this->chat;
        }
    }

    /**
     * This function ensures that the last two function calls don't return
     * the same validation errors. Usually this means that OpenAI is stuck
     * in a loop and can't proceed in the conversation. Proceeding would
     * like lead to the same validation errors again. Exception will be
     * thrown to inform the developer that the prompt or documentation
     * need to be improved.
     */
    protected function noErrorPatternExits(): void
    {
        // get last two function responses
        $messages = collect($this->chat->messages)
            ->filter(fn (ChatMessage $message) => $message->role == ChatRole::FUNCTION)
            ->take(-2)
            ->filter(fn (ChatMessage $message) => isset($message->content['errors']));

        // check if both messages are the same
        if ($messages->count() == 2 && $messages->first()->content == $messages->last()->content) {
            throw ErrorPatternFoundException::create($this->chat->messages);
        }
    }
}