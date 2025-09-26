<?php

namespace MalteKuhr\LaravelGpt\Drivers;

use Exception;
use MalteKuhr\LaravelGpt\Contracts\Driver;
use MalteKuhr\LaravelGpt\Enums\SchemaType;
use Illuminate\Support\Arr;
use MalteKuhr\LaravelGpt\GptAction;
use MalteKuhr\LaravelGpt\Contracts\InputPart;
use MalteKuhr\LaravelGpt\Implementations\Parts\InputFile;
use MalteKuhr\LaravelGpt\Implementations\Parts\InputText;
use MalteKuhr\LaravelGpt\Services\SchemaService\SchemaService;
use Illuminate\Support\Facades\Http;
use MalteKuhr\LaravelGpt\Data\ModelResponse;
use Illuminate\Support\Facades\Log;
use MalteKuhr\LaravelGpt\Data\TokenConfidence;
use Illuminate\Support\Str;

class OpenAIDriver implements Driver
{
    private string $apiKey;
    private string $baseUrl;

    /**
     * Create a new OpenAIDriver instance.
     *
     * @param string $connection
     */
    public function __construct(
        private string $connection
    ) {
        $this->initializeConnection();
    }

    /**
     * Initialize the connection details.
     */
    protected function initializeConnection(): void
    {
        $connection = config('laravel-gpt.connections.' . $this->connection);
        
        if ($connection['api'] === 'azure') {
            $this->baseUrl = "https://{$connection['azure']['resource_name']}.openai.azure.com/openai/deployments/{$connection['azure']['deployment_id']}/chat/completions?api-version={$connection['azure']['api_version']}";
        } else if ($connection['api'] === 'openai') {
            $this->baseUrl = 'https://api.openai.com/v1/chat/completions';
            $this->apiKey = $connection['openai']['api_key'];
        } else {
            $provider = $connection['api'];
            $this->baseUrl = $connection[$provider]['url'];
            $this->apiKey = $connection[$provider]['api_key'];
        }
    }

    /**
     * Run the action and return the response.
     *
     * @param GptAction $action
     * @return ModelResponse
     */
    public function run(GptAction $action): ModelResponse
    {
        // generate the payload for the request
        $payload = $this->generatePayload($action);

        // generate a unique identifier for the request
        $uuid = Str::uuid();

        // log the payload if verbose mode is enabled
        if (config('laravel-gpt.verbose')) {
            Log::info('OpenAI API request (' . $uuid . ')', ['payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        }
        
        // send the request to the OpenAI compatible API
        $response = Http::timeout(120)->withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl, $payload);

        // check if the response is successful
        if ($response->failed()) {
            throw new Exception(sprintf(
                "OpenAI API request failed: %s\nPayload: %s",
                json_encode($response->json()),
                json_encode($payload)
            ));
        }

        // log the response if verbose mode is enabled
        if (config('laravel-gpt.verbose')) {
            Log::info('OpenAI API response (' . $uuid . ')', [
                'url' => $this->baseUrl,
                'status' => $response->status(),
                'data' => json_encode($response->json(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ]);
        }

        // extract the result from the response
        $result = $response->json('choices.0');

        // extract the logprobs and convert them to a percentage
        $logprobs = array_map(function ($logprob) {
            return TokenConfidence::make(
                token: $logprob['token'], 
                confidence: min(100, max(0, round(exp($logprob['logprob']) * 100, 2)))
            );
        }, $result['logprobs']['content']);

        // check if the response is valid JSON
        if ($this->isValidJson($content = $result['message']['content'])) {
            return new ModelResponse(
                output: $content,
                confidence: $logprobs,
                uuid: $uuid
            );
        }

        throw new Exception("The response is not valid JSON.");
    }

    public function training(GptAction $action): array
    {
        $payload = $this->generatePayload($action, training: true);

        return $payload;
    }

    /**
     * Generate the payload for the OpenAI API request.
     *
     * @param GptAction $action
     * @return array
     */
    public function generatePayload(GptAction $action, bool $training = false): array
    {
        $model = $action->model();
        $version = config('laravel-gpt.models')[$model]['version'] ?? $model;
        
        return array_filter([
            'messages' => $this->getMessages($action, training: $training),
            ...(!$training ? [
                'temperature' => $action->temperature(),
                'response_format' => [
                    'type' => 'json_schema',
                    'json_schema' => $this->getJsonSchema($action),
                ],
                'model' => $version,
                'max_tokens' => $action->maxTokens(),
                'logprobs' => true,
            ] : []),
        ], fn ($value) => $value !== null);
    }

    /**
     * Convert input and system message to OpenAI format.
     *
     * @param GptAction $action
     * @return array
     */
    protected function getMessages(GptAction $action, bool $training = false): array
    {
        $messagesPayload = [];

        // add system message if it exists
        if ($action->systemMessage() !== null) {
            $messagesPayload[] = [
                'role' => 'system',
                'content' => $action->systemMessage(),
            ];
        }

        $messagesPayload[] = [
            'role' => 'user',
            'content' => Arr::map($action->parts(), function (InputPart $part) use ($training) {
                if ($part instanceof InputText) {
                    return [
                        'type' => 'text',
                        'text' => $part->text,
                    ];
                } elseif ($part instanceof InputFile) {
                    return [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => $training ? "data:{$part->getMimeType()};base64,{$part->getContent()}" : $part->getFileUrl()
                        ]
                    ];
                } else {
                    throw new Exception("The part type '" . get_class($part) . "' is not supported by the OpenAI driver.");
                }
            }),
        ];

        if ($training) {
            $messagesPayload[] = [
                'role' => 'assistant',
                'content' => $action->output(),
            ];
        }

        return $messagesPayload;
    }

    /**
     * Get the json schema for the OpenAI API request.
     *
     * @param GptAction $action
     * @return array
     */
    protected function getJsonSchema(GptAction $action): array
    {
        $schema = SchemaService::convert($action->rules(), SchemaType::JSON);
        $hash = substr(hash('sha256', json_encode($schema)), 0, 8);

        return [
            'name' => $hash,
            'schema' => $schema,
            'strict' => true,
        ];
    }

    /**
     * Check if a string is valid JSON.
     *
     * @param string $string
     * @return bool
     */
    private function isValidJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}