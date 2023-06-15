<?php

namespace MalteKuhr\LaravelGPT\Tests\Support;

class TestSchema
{
    protected array $schema = [
        'type' => 'object',
        'properties' => [],
        'required' => [],
    ];

    public function __construct(
        protected string $field
    ) {}

    public static function make(string $field = 'test'): self
    {
        return new self($field);
    }

    public function set(string $key, $value): self
    {
        $this->setRecursive($this->schema['properties'][$this->field], $key, $value);
        return $this;
    }

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


    public function required(): self
    {
        $this->schema['required'][] = $this->field;
        return $this;
    }

    public function toArray(): array
    {
        return $this->schema;
    }
}