<?php

namespace MalteKuhr\LaravelGpt\Managers;

use MalteKuhr\LaravelGpt\Events\ChatUpdated;
use MalteKuhr\LaravelGpt\Exceptions\GptChat\ErrorPatternFoundException;
use MalteKuhr\LaravelGpt\Exceptions\GptFunction\FunctionCallRequiresFunctionsException;
use MalteKuhr\LaravelGpt\Exceptions\GptFunction\MissingFunctionException;
use MalteKuhr\LaravelGpt\Facades\FunctionManager;
use MalteKuhr\LaravelGpt\Contracts\BaseChat;
use MalteKuhr\LaravelGpt\Contracts\Driver;
use MalteKuhr\LaravelGpt\Data\Message\Parts\ChatFunctionCall;
use MalteKuhr\LaravelGpt\Enums\FunctionCallStatus;
use MalteKuhr\LaravelGpt\Contracts\ChatMessagePart;
use MalteKuhr\LaravelGpt\Data\Message\ChatMessage;
use MalteKuhr\LaravelGpt\Data\Message\Parts\ChatText;
use MalteKuhr\LaravelGpt\Enums\ChatRole;
use Exception;

class ChatManager
{
    /**
     * Sends the current conversation using the appropriate driver.
     *
     * @param BaseChat $chat
     * @return BaseChat
     * @throws FunctionCallRequiresFunctionsException
     * @throws MissingFunctionException
     * @throws ErrorPatternFoundException
     * @throws Exception
     */
    public function send(BaseChat $chat, int $rotation = 0, bool $sync = true): BaseChat
    {
        if (!$sync) {
            dispatch(fn () => $this->send($chat, $rotation, true));

            return $chat;
        }

        $model = $chat->model();
        $modelConfig = config('laravel-gpt.models')[$model] ?? null;
        $requiredFunctionCall = $chat->functionCall();
        $functions = $chat->functions();

        if (!$modelConfig) {
            throw new Exception("Model '{$model}' not found in configuration.");
        }

        $connection = app("laravel-gpt.{$modelConfig['connection']}");

        if (!$connection instanceof Driver) {
            throw new Exception("Invalid driver for connection '{$modelConfig['connection']}'.");
        }

        // Send chat completion request
        $connection->run(
            chat: $chat,
            streamChat: function (BaseChat $latestChat) use (&$chat) {
                ChatUpdated::dispatch($latestChat);
                $chat = $this->handleLatestMessage($latestChat);
                $chat->save();
            },
        );

        // check if all function calls are complete
        $functionCalls = array_filter($chat->getLatestMessage()->parts, fn (ChatMessagePart $part) => $part instanceof ChatFunctionCall);
        $allComplete = count(array_filter($functionCalls, fn (ChatFunctionCall $functionCall) => $functionCall->status != FunctionCallStatus::COMPLETED)) == 0;

        if (count($functionCalls) > 0 && !$allComplete) {
            $this->rerun($chat, $rotation);
        }

        // Check if a function call was required and if it was actually done
        $latestMessage = $chat->getLatestMessage();
        $functionCalls = array_filter($latestMessage->parts, fn($part) => $part instanceof ChatFunctionCall);

        if (is_array($requiredFunctionCall) || is_string($requiredFunctionCall) || $requiredFunctionCall === true) {


            if (is_array($requiredFunctionCall) || is_string($requiredFunctionCall)) {


                $requiredFunctionClasses = is_string($requiredFunctionCall) ? [$requiredFunctionCall] : $requiredFunctionCall;
                $requiredFunctions = array_filter($functions, fn($function) => in_array(get_class($function), $requiredFunctionClasses));
            } else {
                $requiredFunctions = $chat->functions();
            }

            $allowedFunctionNames = array_map(fn ($class) => $class->name(), $requiredFunctions);
            $calledFunctionNames = array_map(fn ($functionCall) => $functionCall->name, $functionCalls);
            $errorMessage = null;

            if (count($functionsNotCalled = array_diff($allowedFunctionNames, $calledFunctionNames)) > 0) {
                // throw error when not allowed function was called
                $errorMessage = "The following functions were not called: " . implode(', ', $functionsNotCalled);           
            } else if (count($allowedFunctionNames) > 0 && count($calledFunctionNames) == 0) {
                // throw error when no function was called
                $errorMessage = "No function was called. You have to call at least one of the following functions: " . implode(', ', $allowedFunctionNames);
            } else {
            }
            
            if (!is_null($errorMessage)) {
                $chat->addMessage(new ChatMessage(
                    role: ChatRole::USER,
                    parts: [new ChatText(text: 'Error: ' . $errorMessage)]
                ));

                $this->rerun($chat);
            }
        }

        return $chat;
    }
    /**
     * Rerun the chat with an incremented rotation count.
     *
     * @param BaseChat $chat The chat to rerun
     * @param int $rotation The current rotation count
     * @return BaseChat The updated chat after rerunning
     * @throws Exception If the maximum number of rotations is reached
     */
    protected function rerun(BaseChat $chat, int $rotation = 0): BaseChat
    {
        if ($rotation >= config('laravel-gpt.max-rotations')) {
            throw new Exception("Maximum number of function calls in one rotation reached. Check max_rotations in your config.");
        }

        return $this->send($chat, rotation: $rotation + 1);
    }

    /**
     * Handles the latest message in the chat.
     *
     * @param BaseChat $chat
     * @return BaseChat
     */
    protected function handleLatestMessage(BaseChat $chat): BaseChat
    {
        $latestMessage = $chat->getLatestMessage();
        $newFunctionCalls = array_filter($latestMessage->parts, function (ChatMessagePart $part) {
            return $part instanceof ChatFunctionCall && $part->status === FunctionCallStatus::NEW;
        });

        /** @var ChatFunctionCall $functionCall */
        foreach ($newFunctionCalls as $functionCall) {
            $index = array_search($functionCall, $latestMessage->parts);

            $chat->updateLatestMessage(
                $latestMessage->replacePart(
                    index: $index,
                    part: $functionCall->updateStatus(FunctionCallStatus::PENDING)
                )
            );

            $chat->updateLatestMessage(
                $latestMessage->replacePart(
                    index: $index,
                    part: FunctionManager::call($chat, $functionCall)
                )
            );
        }

        return $chat;
    }
}