<?php

namespace MalteKuhr\LaravelGPT\Services\SchemaService;

use Illuminate\Support\Arr;
use MalteKuhr\LaravelGPT\Exceptions\SchemaService\FieldSetException;
use MalteKuhr\LaravelGPT\Enums\SchemaType;

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
        if (isset($schema['type']) && $schema['type'] != $type) {
            throw FieldSetException::create('type');
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
        $ruleClasses = collect(config('laravel-gpt.rules'))->sortByDesc(fn ($ruleClass) => $ruleClass::priority())->values();

        foreach ($ruleClasses as $ruleClass) {
            $schema = $ruleClass::run($schema, $path, $rules, $schemaType);
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
            $schema['type'] = strtoupper($schema['type']);
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