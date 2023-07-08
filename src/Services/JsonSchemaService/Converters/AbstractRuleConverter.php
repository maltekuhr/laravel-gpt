<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters;

use MalteKuhr\LaravelGPT\Exceptions\JsonSchemaService\FieldSetException;

abstract class AbstractRuleConverter
{
    public function __construct(
        protected array $schema,
        protected string $path,
        protected array $rules
    ) {}

    /**
     * @param array $schema
     * @param string $path
     * @param array $rules
     * @return array
     */
    public static function run(array $schema, string $path, array $rules): array
    {
        $rule = new static($schema, $path, $rules);
        $rule->handle();
        return $rule->schema;
    }

    /**
     * Gets the priority of the rule. Rules with higher priority are processed
     * first. This function returns 0 by default, but it can be overridden in
     * child classes.
     *
     * @return int The priority of the rule.
     */
    public static function priority(): int
    {
        return 0;
    }

    /**
     * Handle the provided schema and rules. This method must be implemented by
     * each concrete rule class.
     */
    abstract public function handle(): void;

    /**
     * Adds a description to the schema. If a description is already set, the new
     * description is appended to the existing one.
     *
     * @param string $description
     * @return void
     */
    public function addDescription(string $description): void
    {
        if ($this->getField('description') !== null) {
            $this->setField('description', $this->getField('description') . '; ' . $description, true);
        } else {
            $this->setField('description', $description);
        }
    }

    /**
     * Sets the type of the schema. If the type is already set, an exception is
     * thrown unless the override flag is set to true.
     *
     * @param string $type
     * @param bool $override
     * @return void
     * @throws FieldSetException
     */
    public function setType(string $type, bool $override = false): void
    {
        $this->setField('type', $type, $override);
    }


    /**
     * Returns the type of the field.
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->getField('type');
    }


    /**
     * Sets the value of a field in the schema. If the field is
     * already set, an exception is thrown unless the override
     * flag is set to true.
     *
     * @param string $field
     * @param $value
     * @param bool $override
     * @return void
     * @throws FieldSetException
     */
    public function setField(string $field, $value, bool $override = false): void
    {
        if (!$override && $this->getField($field) !== null && $this->getField($field) != $value) {
            throw FieldSetException::create($field);
        }

        if (($this->schema['type'] ?? '') == 'array') {
            $this->setRecursive($this->schema['items'], $field, $value);
        } else if (($this->schema['type'] ?? 'object') == 'object') {
            $this->schema['type'] = 'object';
            $this->setRecursive($this->schema['properties'][$this->path], $field, $value);
        }
    }

    /**
     * @param $arr
     * @param $path
     * @param $value
     * @return void
     */
    private function setRecursive(&$arr, $path, $value)
    {
        $keys = explode('.', $path);

        while ($key = array_shift($keys)) {
            if (count($keys)) {
                if (!isset($arr[$key]) || !is_array($arr[$key])) {
                    $arr[$key] = [];
                }
                $arr = &$arr[$key];
            } else {
                $arr[$key] = $value;
            }
        }
    }

    /**
     * Returns the value of a field
     *
     * @param string $field
     * @return mixed
     */
    public function getField(string $field): mixed
    {
        if (($this->schema['type'] ?? '') == 'array') {
            return $this->getRecursive($this->schema['items'] ?? [], $field);
        } else {
            return $this->getRecursive($this->schema['properties'][$this->path] ?? [], $field);
        }
    }

    /**
     * @param $arr
     * @param $path
     * @return mixed|null
     */
    private function getRecursive($arr, $path)
    {
        $keys = explode('.', $path);

        while ($key = array_shift($keys)) {
            if (isset($arr[$key])) {
                if (count($keys)) {
                    if (is_array($arr[$key])) {
                        $arr = $arr[$key];
                    } else {
                        return null;
                    }
                } else {
                    return $arr[$key];
                }
            } else {
                return null;
            }
        }

        return null;
    }
}
