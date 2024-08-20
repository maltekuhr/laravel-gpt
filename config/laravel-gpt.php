<?php

use MalteKuhr\LaravelGPT\Services\SchemaService\Converters\RuleConverters\{
    AcceptedIfRuleConverter,
    AcceptedRuleConverter,
    AfterOrEqualRuleConverter,
    AfterRuleConverter,
    AlphaDashRuleConverter,
    AlphaNumRuleConverter,
    AlphaRuleConverter,
    ArrayRuleConverter,
    AsciiRuleConverter,
    BeforeOrEqualRuleConverter,
    BeforeRuleConverter,
    BetweenRuleConverter,
    BooleanRuleConverter,
    DateRuleConverter,
    DecimalRuleConverter,
    EmailRuleConverter,
    EnumRuleConverter,
    FieldDescriptionRuleConverter,
    InRuleConverter,
    IntegerRuleConverter,
    MaxRuleConverter,
    MinRuleConverter,
    NotInRuleConverter,
    RequiredIfRuleConverter,
    RequiredRuleConverter,
    StringRuleConverter,
    UrlRuleConverter,
};

return [
    /*
    |--------------------------------------------------------------------------
    | Default Model
    |--------------------------------------------------------------------------
    |
    | The default model used in the requests against the OpenAI API. You can
    | override the model in the GPTAction and GPTChat using the `model()`
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
    | File Converters
    |--------------------------------------------------------------------------
    |
    | This section defines the file converters used to transform certain file
    | types into formats supported by the AI models. Each converter specifies
    | the input file type, output format, and the class responsible for
    | performing the conversion.
    |
    */

    'file_converters' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | Max Rotations
    |--------------------------------------------------------------------------
    |
    | This section defines the maximum number of function call messages the
    | assistant can make before taking action based on the configuration.
    | This helps prevent infinite loops and excessive API usage. The limit
    | is applied globally, but the behavior differs based on the context.
    |
    | When possible, the chat will try to force the model to send a message.
    | When this is not possible due to enforced function calling (like in
    | GPTActions or in chats with forced function calling), the chat will
    | throw an exception.
    |
    */

    'max-rotations' => 10,


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
    | schema format understood by GPT-3.5 and GPT-4. You can add your own rule
    | converters here, or remove the default ones if you don't need them.
    */
    'rules' => [
        // custom rule converters

        // default rule converters
        AcceptedIfRuleConverter::class,
        AcceptedRuleConverter::class,
        AfterOrEqualRuleConverter::class,
        AfterRuleConverter::class,
        AlphaDashRuleConverter::class,
        AlphaNumRuleConverter::class,
        AlphaRuleConverter::class,
        ArrayRuleConverter::class,
        AsciiRuleConverter::class,
        BeforeOrEqualRuleConverter::class,
        BeforeRuleConverter::class,
        BetweenRuleConverter::class,
        BooleanRuleConverter::class,
        DateRuleConverter::class,
        DecimalRuleConverter::class,
        EmailRuleConverter::class,
        EnumRuleConverter::class,
        FieldDescriptionRuleConverter::class,
        InRuleConverter::class,
        IntegerRuleConverter::class,
        MaxRuleConverter::class,
        MinRuleConverter::class,
        NotInRuleConverter::class,
        RequiredIfRuleConverter::class,
        RequiredRuleConverter::class,
        StringRuleConverter::class,
        UrlRuleConverter::class,
    ],
];