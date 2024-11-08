<?php

namespace MalteKuhr\LaravelGpt\Drivers;

use MalteKuhr\LaravelGpt\Data\Message\ChatMessage;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use MalteKuhr\LaravelGpt\Contracts\Driver;
use MalteKuhr\LaravelGpt\Contracts\BaseChat;
use MalteKuhr\LaravelGpt\Enums\ChatRole;
use MalteKuhr\LaravelGpt\Enums\SchemaType;
use MalteKuhr\LaravelGpt\GptFunction;
use MalteKuhr\LaravelGpt\Facades\FunctionManager;
use MalteKuhr\LaravelGpt\Data\Message\Parts\ChatFunctionCall;
use MalteKuhr\LaravelGpt\Data\Message\Parts\ChatText;
use MalteKuhr\LaravelGpt\Data\Message\Parts\ChatFile;
use MalteKuhr\LaravelGpt\Exceptions\GptFunction\MissingFunctionException;
use MalteKuhr\LaravelGpt\Exceptions\GptFunction\FunctionCallRequiresFunctionsException;
use MalteKuhr\LaravelGpt\Data\ModelResponse;
use MalteKuhr\LaravelGpt\Data\TokenConfidence;
use MalteKuhr\LaravelGpt\Services\SchemaService\SchemaService;
use Illuminate\Support\Arr;
use MalteKuhr\LaravelGpt\GptAction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use MalteKuhr\LaravelGpt\Implementations\Parts\InputFile;
use MalteKuhr\LaravelGpt\Implementations\Parts\InputText;
use MalteKuhr\LaravelGpt\Contracts\InputPart;
use Illuminate\Support\Facades\Http;
use Exception;

class GeminiDriver implements Driver
{
    private string $connection;
    private string $apiKey;

    /**
     * Create a new GeminiDriver instance.
     *
     * @param string $connection
     */
    public function __construct(string $connection)
    {
        $this->connection = $connection;
        $this->apiKey = config("laravel-gpt.connections.$connection")['api_key'];
    }

    /**
     * Run the action and return the response.
     *
     * @param GptAction $action
     * @return ModelResponse
     */
    public function run(GptAction $action): ModelResponse
    {
        // get the model
        $model = $action->model();
        $version = config('laravel-gpt.models')[$model]['version'] ?? $model;

        // generate the payload
        $payload = $this->generatePayload($action);

        // generate a unique identifier for the request
        $uuid = Str::uuid();

        if (config('laravel-gpt.verbose')) {
            Log::info('Gemini API request (' . $uuid . ')', ['payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        }

        // send the request to the Gemini API
        $response = Http::withHeaders([
            'x-goog-api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$version}:generateContent", $payload);
                

        // check if the response is successful
        if ($response->failed()) {
            $error = $response->json('error');
            throw new Exception("Gemini API request failed: " . json_encode($error));
        }

        // log the response if verbose mode is enabled
        if (config('laravel-gpt.verbose')) {
            Log::info('Gemini API response (' . $uuid . ')', [
                'status' => $response->getStatusCode(),
                'data' => json_encode($response->json(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ]);
        }

        // extract the result from the response
        $candidate = $response->json('candidates.0');
        $content = $candidate['content']['parts'][0]['text'];

        // Extract token confidences from logprobs
        $logprobs = array_map(function ($logprob) {
            return TokenConfidence::make(
                token: $logprob['token'],
                confidence: min(100, max(0, round(exp($logprob['logProbability']) * 100, 2)))
            );
        }, $candidate['logprobsResult']['chosenCandidates'] ?? []);

        return new ModelResponse(
            output: $content,
            confidence: $logprobs,
            uuid: $uuid
        );
    }

    public function training(GptAction $action): ?string
    {
        return null;
    }

    /**
     * Generate the payload for the Gemini API request.
     *
     * @param BaseChat $chat
     * @return array
     */
    protected function generatePayload(GptAction $action): array
    {
        return [
            'systemInstruction' => [
                'parts' => [[
                    'text' => $action->systemMessage(),
                ]]
            ],
            'contents' => $this->getContents($action),
            'generationConfig' => [
                'temperature' => $action->temperature(),
                'maxOutputTokens' => $action->maxTokens(),
                'responseSchema' => SchemaService::convert($action->rules(), SchemaType::OPEN_API),
                'responseMimeType' => 'application/json',
                'responseLogprobs' => true,
            ],

        ];
    }

    /**
     * Convert chat messages to Gemini format.
     *
     * @param GptAction $action
     * @return array
     */
    protected function getContents(GptAction $action): array
    {
        return [[
            'role' => 'user',
            'parts' => Arr::map($action->parts(), function (InputPart $part) {
                if ($part instanceof InputText) {
                    return [
                        'text' => $part->text
                    ];
                } elseif ($part instanceof InputFile) {
                    return [
                        'inlineData' => [
                            'mimeType' => $part->getMimeType(),
                            'data' => $part->getContent()
                        ]
                    ];
                } else {
                    throw new Exception("The part type '" . get_class($part) . "' is not supported by the OpenAI driver.");
                }
            }),
        ]];
    }
}