<?php

use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\AcceptedIfRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\AcceptedRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\AfterOrEqualRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\AfterRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\AlphaDashRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\AlphaNumRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\AlphaRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\AsciiRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\BeforeOrEqualRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\BeforeRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\BetweenRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\BooleanRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\DateRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\DecimalRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\EmailRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\EnumRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\FieldDescriptionRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\InRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\IntegerRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\RequiredIfRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\RequiredRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\StringRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\UrlRuleConverter;

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key and Organization
    |--------------------------------------------------------------------------
    |
    | Here you may specify your OpenAI API Key and organization. This will be
    | used to authenticate with the OpenAI API - you can find your API key
    | and organization on your OpenAI dashboard, at https://openai.com.
    */

    'api_key' => env('OPENAI_API_KEY'),
    'organization' => env('OPENAI_ORGANIZATION'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout may be used to specify the maximum number of seconds to wait
    | for a response. By default, the client will time out after 30 seconds.
    */

    'request_timeout' => env('OPENAI_REQUEST_TIMEOUT', 30),

    'rules' => [
        AcceptedIfRuleConverter::class,
        AcceptedRuleConverter::class,
        AfterOrEqualRuleConverter::class,
        AfterRuleConverter::class,
        AlphaDashRuleConverter::class,
        \MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\ArrayRuleConverter::class,
        AlphaNumRuleConverter::class,
        AlphaRuleConverter::class,
        AsciiRuleConverter::class,
        BeforeRuleConverter::class,
        BeforeOrEqualRuleConverter::class,
        BetweenRuleConverter::class,
        BooleanRuleConverter::class,
        DateRuleConverter::class,
        DecimalRuleConverter::class,
        EmailRuleConverter::class,
        EnumRuleConverter::class,
        FieldDescriptionRuleConverter::class,
        InRuleConverter::class,
        IntegerRuleConverter::class,
        RequiredIfRuleConverter::class,
        RequiredRuleConverter::class,
        StringRuleConverter::class,
        UrlRuleConverter::class,
        \MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\MinRuleConverter::class,
        \MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\MaxRuleConverter::class,
        \MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters\NotInRuleConverter::class

    ]
];