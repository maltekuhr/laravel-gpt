<?php

namespace MalteKuhr\LaravelGPT\Managers;

use MalteKuhr\LaravelGPT\Events\ChatUpdated;
use MalteKuhr\LaravelGPT\Exceptions\GPTChat\ErrorPatternFoundException;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\FunctionCallRequiresFunctionsException;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\MissingFunctionException;
use MalteKuhr\LaravelGPT\Facades\FunctionManager;
use MalteKuhr\LaravelGPT\GPTChat;
use MalteKuhr\LaravelGPT\Contracts\Driver;
use MalteKuhr\LaravelGPT\Data\Message\Parts\ChatFunctionCall;
use MalteKuhr\LaravelGPT\Enums\FunctionCallStatus;
use MalteKuhr\LaravelGPT\Contracts\ChatMessagePart;

use Exception;

class ChatManager
{
    /**
     * Sends the current conversation using the appropriate driver.
     *
     * @param GPTChat $chat
     * @return GPTChat
     * @throws FunctionCallRequiresFunctionsException
     * @throws MissingFunctionException
     * @throws ErrorPatternFoundException
     * @throws Exception
     */
    public function send(GPTChat $chat, int $rotation = 0, bool $sync = false): GPTChat
    {
        if (!$sync) {
            dispatch(fn () => $this->send($chat, $rotation, true));

            return $chat;
        }

        // Listen sending hook
        if (method_exists($chat, 'sending') && !$chat->sending()) {
            return $chat;
        }

        $model = $chat->model();
        $modelConfig = config('laravel-gpt.models')[$model] ?? null;

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
            streamChat: function (GPTChat $latestChat) use ($chat) {
                $chat = $latestChat;
                ChatUpdated::dispatch($chat);
                $this->handleLatestMessage($chat);
            },
        );

        // check if all function calls are complete
        $functionCalls = array_filter($chat->latestMessage()->parts, fn (ChatMessagePart $part) => $part instanceof ChatFunctionCall);
        $allComplete = count(array_filter($functionCalls, fn (ChatFunctionCall $functionCall) => !in_array($functionCall->status, [FunctionCallStatus::COMPLETED, FunctionCallStatus::ERROR]))) == 0;

        if (count($functionCalls) > 0 && $allComplete) {
            if ($rotation >= config('laravel-gpt.max-rotations')) {
                throw new Exception("Maximum number of function call in one rotation reached. Check max_rotations in your config.");
            }

            return $this->send($chat, rotation: $rotation + 1);
        }

        return $chat;
    }

    /**
     * Handles the latest message in the chat.
     *
     * @param GPTChat $chat
     * @return void
     */
    protected function handleLatestMessage(GPTChat $chat): void
    {
        $latestMessage = $chat->latestMessage();

        $newFunctionCalls = array_filter($latestMessage->parts, function (ChatMessagePart $part) {
            return $part instanceof ChatFunctionCall && $part->status === FunctionCallStatus::NEW;
        });

        /** @var ChatFunctionCall $functionCall */
        foreach ($newFunctionCalls as $functionCall) {
            $index = array_search($functionCall, $latestMessage->parts);

            $chat->replaceLatest($latestMessage->replacePart(
                index: $index,
                part: $functionCall->updateStatus(FunctionCallStatus::PENDING)
            ));

            $chat->replaceLatest($latestMessage->replacePart(
                index: $index,
                part: FunctionManager::call($chat, $functionCall)
            ));
        }
    }
}