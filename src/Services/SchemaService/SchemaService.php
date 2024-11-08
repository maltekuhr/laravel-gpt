<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService;

use Illuminate\Support\Arr;
use MalteKuhr\LaravelGpt\Exceptions\SchemaService\FieldSetException;
use MalteKuhr\LaravelGpt\Enums\SchemaType;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\AcceptedIfRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\AcceptedRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\AfterOrEqualRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\AfterRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\AlphaDashRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\AlphaNumRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\AlphaRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\ArrayRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\AsciiRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\BeforeOrEqualRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\BeforeRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\BetweenRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\BooleanRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\DateRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\DecimalRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\EmailRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\EnumRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\FieldDescriptionRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\InRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\IntegerRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\MaxRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\MinRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\NotInRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\NullableRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\StringRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\UrlRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters\RegexRuleConverter;

class SchemaService
{
    /**
     * Default template for the schema.
     *
     * @var array
     */
    public array $schema = [
        'type' => 'object',
        'properties' => [],
        'required' => [],
        'additionalProperties' => false,
    ];

    public static array $rules = [
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
        NullableRuleConverter::class,
        StringRuleConverter::class,
        UrlRuleConverter::class,
        RegexRuleConverter::class,
    ];

    /**
     * Converts Laravel validation rules into the required schema.
     *
     * @param array $rules
     * @param SchemaType $schemaType
     * @return array
     */
    public static function convert(array $rules, SchemaType $schemaType): array
    {
        // create a new instance of the converter
        $converter = new self();
        // order rules by number of dots to ensure that parent fields are generated first
        $rules = Arr::sort($rules, function ($value, $key) {
            return substr_count($key, '.');
        });

        // iterate over all rules
        foreach ($rules as $field => $fieldRules) {
            // convert rules into consistent format (array)
            if (is_string($fieldRules)) {
                $fieldRules = explode('|', $fieldRules);
            }

            // get the path of the field (e.g. 'user.name' -> ['user', 'name'])
            $path = explode('.', $field);

            // generate the schema for the field
            $converter->schema = $converter->generateSchema(
                schema: $converter->schema ?? [],
                path: $path,
                rules: $fieldRules,
                schemaType: $schemaType
            );
        }

        if ($schemaType === SchemaType::OPEN_API) {
            $converter->schema = $converter->uppercaseTypes($converter->schema);
            unset($converter->schema['additionalProperties']);
        } else {
            $converter->schema['additionalProperties'] = false;
            $converter->schema['required'] = array_unique(array_keys($converter->schema['properties']));        
        }


        return $converter->schema;
    }

    /**
     * Generates the schema for the given path and rules.
     *
     * @param array $schema
     * @param array $path
     * @param array $rules
     * @param SchemaType $schemaType
     * @return array
     */
    protected function generateSchema(array $schema, array $path, array $rules, SchemaType $schemaType): array
    {
        // process the rules if the path is not nested
        if (count($path) == 1) {
            return $this->processRules($schema, $path[0], $rules, $schemaType);
        }

        // get reference to children schema path
        if (mb_strtolower($schema['type']) == 'object') {
            $schemaRef = &$schema['properties'][$path[0]];
        } else {
            $schemaRef = &$schema['items'];
        }

        // determine the type of the child schema
        $childType = $path[1] == '*' ? 'array' : 'object';

        // create the child schema if it does not exist
        $schemaRef = $this->setSchemaType($schemaRef ?? [], $childType, $schemaType);

        // generate the child schema
        $schemaRef = $this->generateSchema(
            schema: $schemaRef,
            path: array_slice($path, 1),
            rules: $rules,
            schemaType: $schemaType
        );

        return $schema;
    }

    /**
     * Sets the type of the schema without overwriting the existing type.
     *
     * @param array $schema
     * @param string $type
     * @param SchemaType $schemaType
     * @return array
     * @throws FieldSetException
     */
    protected function setSchemaType(array $schema, string $type, SchemaType $schemaType): array
    {
        if (isset($schema['type']) && strtolower($schema['type']) != strtolower($type)) {
            throw FieldSetException::create('type');
        }

        $schema['type'] = $schemaType === SchemaType::OPEN_API ? strtoupper($type) : $type;

        if ($type === 'object' && $schemaType === SchemaType::JSON) {
            $schema['additionalProperties'] = false;
        }

        return $schema;
    }

    /**
     * Processes the rules for the given path.
     *
     * @param array $schema
     * @param string $path
     * @param array $rules
     * @param SchemaType $schemaType
     * @return array
     */
    private function processRules(array $schema, string $path, array $rules, SchemaType $schemaType): array
    {
        $ruleClasses = collect(config('laravel-gpt.rules'))->merge(self::$rules)->sortByDesc(fn ($ruleClass) => $ruleClass::priority())->values();

        foreach ($ruleClasses as $ruleClass) {
            $schema = $ruleClass::run($schema, $path, $rules, $schemaType);
        }

        if (strtolower($schema['type']) === 'object') {
            $schema['required'] = array_keys($schema['properties']);

            if ($schemaType === SchemaType::JSON) {
                $schema['additionalProperties'] = false;
            }
        }

        return $schema;
    }

    /**
     * Recursively uppercase all types in the schema.
     *
     * @param array $schema
     * @return array
     */
    private function uppercaseTypes(array $schema): array
    {
        if (isset($schema['type'])) {
            if (is_array($schema['type'])) {
                $schema['type'] = array_map(fn ($type) => strtoupper($type), $schema['type']);
            } else {
                $schema['type'] = strtoupper($schema['type']);
            }
        }

        if (isset($schema['properties']) && is_array($schema['properties'])) {
            foreach ($schema['properties'] as $key => $value) {
                $schema['properties'][$key] = $this->uppercaseTypes($value);
            }
        }

        if (isset($schema['items']) && is_array($schema['items'])) {
            $schema['items'] = $this->uppercaseTypes($schema['items']);
        }

        return $schema;
    }
}