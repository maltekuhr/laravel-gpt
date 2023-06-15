<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService;

use Illuminate\Support\Arr;
use MalteKuhr\LaravelGPT\Exceptions\JsonSchemaService\FieldSetException;

class JsonSchemaService
{
    /**
     * Default template for the json schema.
     *
     * @var array
     */
    public array $jsonSchema = [
        'type' => 'object',
        'properties' => [],
        'required' => [],
    ];

    /**
     * Converts Laravel validation rules into the of OpenAI required
     * json schema.
     *
     * @param array $rules
     * @return void
     */
    public static function convert(array $rules): array
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
            $converter->jsonSchema = $converter->generateSchema(
                schema: $converter->jsonSchema ?? [],
                path: $path,
                rules: $fieldRules
            );
        }

        return $converter->jsonSchema;
    }

    /**
     * Generates the json schema for the given path and rules.
     *
     * @param array $schema
     * @param array $path
     * @param array $rules
     * @return array
     */
    protected function generateSchema(array $schema, array $path, array $rules): array
    {
        // process the rules if the path is not nested
        if (count($path) == 1) {
            return $this->processRules($schema, $path[0], $rules);
        }

        // get reference to children schema path
        if ($schema['type'] == 'object') {
            $schemaRef = &$schema['properties'][$path[0]];
        } else {
            $schemaRef = &$schema['items'];
        }

        // determine the type of the child schema
        $childType = $path[1] == '*' ? 'array' : 'object';

        // create the child schema if it does not exist
        $schemaRef = $this->setSchemaType($schemaRef ?? [], $childType);

        // generate the child schema
        $schemaRef = $this->generateSchema(
            schema: $schemaRef,
            path: array_slice($path, 1),
            rules: $rules
        );

        return $schema;
    }

    /**
     * Sets the type of the schema without overwriting the existing type.
     *
     * @param array $schema
     * @param string $type
     * @return array
     * @throws FieldSetException
     */
    protected function setSchemaType(array $schema, string $type): array
    {
        if (isset($schema['type']) && $schema['type'] != $type) {
            throw FieldSetException::create('type');
        }

        $schema['type'] = $type;
        return $schema;
    }

    /**
     * Processes the rules for the given path.
     *
     * @param array $schema
     * @param string $path
     * @param array $rules
     * @return array
     */
    private function processRules(array $schema, string $path, array $rules): array
    {
        $ruleClasses = collect(config('laravel-gpt.rules'))->sortByDesc(fn ($ruleClass) => $ruleClass::priority())->values();

        foreach ($ruleClasses as $ruleClass) {
            $schema = $ruleClass::run($schema, $path, $rules);
        }

        return $schema;
    }
}