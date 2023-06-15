<?php

namespace MalteKuhr\LaravelGPT\Managers;

use Illuminate\Support\Arr;
use MalteKuhr\LaravelGPT\Enums\ChatRole;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\FunctionCallDecodingException;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\FunctionCallRequiresFunctionsException;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\MissingFunctionException;
use MalteKuhr\LaravelGPT\Facades\OpenAI;
use MalteKuhr\LaravelGPT\Generators\ChatPayloadGenerator;
use MalteKuhr\LaravelGPT\GPTFunction;
use MalteKuhr\LaravelGPT\GPTRequest;
use MalteKuhr\LaravelGPT\Models\ChatMessage;
use OpenAI\Exceptions\TransporterException;
use OpenAI\Responses\Chat\CreateResponseMessage;

class ChatManager
{

    /**
     * Sends the current conversation against the OpenAI Chat Completion API.
     *
     * @param GPTRequest $request
     * @return GPTRequest
     * @throws FunctionCallRequiresFunctionsException
     * @throws MissingFunctionException
     * @throws TransporterException
     */
    public static function send(GPTRequest $request): GPTRequest
    {
        // listen sending hook
        if (!$request->sending()) {
            return $request;
        }

        // send chat completion request
        $answer = OpenAI::chat()->create(
            ChatPayloadGenerator::generate($request)
        )->choices[0]->message;

        // handle the response
        self::handleResponse($request, $answer);

        // listen received hook
        if (!$request->received()) {
            return $request;
        }

        // handle next steps
        $latestMessage = $request->latestMessage();
        if ($latestMessage->role == ChatRole::ASSISTANT && $latestMessage->functionCall != null) {
            return self::handleFunctionCall($request, $latestMessage);
        } else {
            return $request;
        }
    }

    /**
     * Handles the response from the OpenAI Chat Completion API.
     *
     * @param GPTRequest $request
     * @param CreateResponseMessage $answer
     * @return void
     */
    protected static function handleResponse(GPTRequest $request, CreateResponseMessage $answer): void
    {
        try {
            $request->addMessage(
                ChatMessage::fromResponseMessage($answer)
            );
        } catch (FunctionCallDecodingException $exception) {
            $request->addMessage(
                ChatMessage::from(
                    role: ChatRole::ASSISTANT,
                    content: json_encode($answer->functionCall)
                )
            );

            $request->addMessage(
                ChatMessage::from(
                    role: ChatRole::FUNCTION,
                    content: $exception->getMessage(),
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
     * @param GPTRequest $request
     * @param ChatMessage $answer
     * @return GPTRequest
     * @throws FunctionCallRequiresFunctionsException
     * @throws MissingFunctionException
     */
    protected static function handleFunctionCall(GPTRequest $request, ChatMessage $answer): GPTRequest
    {
        // get the object of the function
        $function = Arr::first(
            array: $request->functions(),
            callback: fn (GPTFunction $function) => $function->name() == $answer->functionCall->name
        );

        // make sure that the function exists
        if ($function == null) {
            $request->addMessage(
                ChatMessage::from(
                    role: ChatRole::FUNCTION,
                    content: 'Function not found.',
                    name: $answer->functionCall->name
                )
            );

            return $request;
        }

        // call function and add result to conversation
        $request->addMessage(
            FunctionManager::call($function, $answer->functionCall->arguments)
        );

        // make sure that the next request isn't the same
        $isForced = is_string($request->functionCall()) && get_class($function) == $request->functionCall();

        // check if function call has response and wasn't forced
        if ($request->latestMessage()->content !== null && !$isForced) {
            // proceed in conversation with model
            return self::send($request);
        } else {
            return $request;
        }
    }
}