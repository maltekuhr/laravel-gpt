<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Model
    |--------------------------------------------------------------------------
    |
    | The default model used in the requests against the OpenAI API. You can
    | override the model in the GptAction and GptChat using the `model()`
    | method.
    */

    'default_model' => 'gpt-4o-mini',

    /*
    |--------------------------------------------------------------------------
    | Available Models
    |--------------------------------------------------------------------------
    |
    | This array defines the available AI models that can be used with the
    | package. Each model is configured with its connection type. You can add
    | or modify models as needed for your application.
    |
    | Note: Ensure that you have the necessary API access and credentials
    | configured for each connection type (openai, gemini, anthropic, etc.)
    | in your environment or services configuration.
    */

    'models' => [
        'gpt-4o' => [
            'connection' => 'openai',
        ],
        'gpt-4o-mini' => [
            'connection' => 'openai',
        ],
        'gemini-1.5-pro' => [
            'connection' => 'gemini',
        ],
        'gemini-1.5-flash' => [
            'connection' => 'gemini',
        ],
        'claude-3.5-sonnet' => [
            'connection' => 'anthropic',
        ],
        'claude-3-haiku' => [
            'connection' => 'anthropic',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Disk
    |--------------------------------------------------------------------------
    |
    | The disk used to store files used in chat messages. It is recommended to
    | use a s3 disk for this. If not make sure the disk can be accessed from
    | the internet.
    |
    */

    'disk' => [
        'name' => 'private',
        'prefix' => 'gpt-chats',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | This option specifies which database connection to use for storing
    | Laravel GPT related data. By default, it uses the application's default
    | database connection. You can specify a different connection if needed.
    |
    */

    'database' => [
        'connection' => env('GPT_DB_CONNECTION', config('database.default')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Connections
    |--------------------------------------------------------------------------
    |
    | This section defines the connections to various AI service providers.
    | Each connection specifies the driver and necessary configuration
    | details for authenticating and communicating with the AI service.
    |
    */

    'connections' => [
        'openai' => [
            'driver' => 'openai',
            'api' => 'openai', // available apis: openai, azure (you can remove the ones you don't use)
            'openai' => [
                'api_key' => env('OPENAI_API_KEY'),
            ],
            'azure' => [
                'resource_name' => env('AZURE_RESOURCE_NAME'),
                'deployment_id' => env('AZURE_DEPLOYMENT_ID'),
                'api_version' => env('AZURE_API_VERSION', '2024-06-01'),
                'api_key' => env('AZURE_API_KEY'),
            ],
            'request_timeout' => env('OPENAI_REQUEST_TIMEOUT', 30),
        ],

        'gemini' => [
            'driver' => 'gemini',
            'api_key' => env('GEMINI_API_KEY')
        ],

        'anthropic' => [
            'driver' => 'anthropic',
            'api' => 'anthropic', // available apis: aws, anthropic, google (you can remove the ones you don't use)
            'anthropic' => [
                'api_key' => env('ANTHROPIC_API_KEY'),
                'version' => [
                    'claude-3.5-sonnet' => 'claude-3-5-sonnet-20240620',
                    'claude-3-haiku' => 'claude-3-haiku-20240307',
                ],
            ],
            'google' => [
                'api_key' => env('GOOGLE_API_KEY'),
                'location' => 'us-central1',
                'versions' => [
                    'claude-3.5-sonnet' => 'claude-3-5-sonnet@20240620',
                    'claude-3-haiku' => 'claude-3-haiku@20240307',
                ],
            ],
            'aws' => [
                'access_key_id' => env('AWS_ACCESS_KEY_ID'),
                'secret_access_key' => env('AWS_SECRET_ACCESS_KEY'),
                'region' => 'us-east-1',
                'versions' => [
                    'claude-3.5-sonnet' => 'anthropic.claude-3-5-sonnet-20240620-v1:0',
                    'claude-3-haiku' => 'anthropic.claude-3-haiku-20240307-v1:0',
                ],
            ],
            'request_timeout' => env('ANTHROPIC_REQUEST_TIMEOUT', 30),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rule Converters
    |--------------------------------------------------------------------------
    |
    | Rule converters are used to convert Laravel validation rules to the JSON
    | schema format understood by Gpt-3.5 and Gpt-4. You can add your own rule
    | converters here, or remove the default ones if you don't need them.
    */
    'rules' => [
        // custom rule converters
        // AcceptedIfRuleConverter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Verbose Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, this setting will cause the package to log detailed information
    | about API requests and responses. This can be helpful for debugging but
    | should typically be disabled in production environments.
    |
    */

    'verbose' => env('APP_DEBUG', false),
];