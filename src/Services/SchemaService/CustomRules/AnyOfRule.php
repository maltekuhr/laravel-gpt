<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\CustomRules;

use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class AnyOfRule implements ValidationRule
{
    protected array $typeRules;
    protected string $typeKey;
    protected array $errors = [];
    protected string $path = '';

    public function __construct(array $typeRules, string $typeKey = 'type')
    {
        $this->typeRules = $typeRules;
        $this->typeKey = $typeKey;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value)) {
            $fail('The input must be an array.');
            return;
        }
        
        if (!array_key_exists($type = $value['type'], $this->typeRules)) {
            $fail("Invalid type: {$type}. Allowed types: " . implode(', ', array_keys($this->typeRules)));
            return;
        }

        $validator = Validator::make($value, $this->typeRules[$type]);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $messages) {
                foreach ($messages as $msg) {
                    $fail("{$attribute}.{$key}: {$msg}");
                }   
            }
        }
    }

    public function getTypes(): array
    {
        return array_keys($this->typeRules);
    }

    public function getRules(string $type): array
    {
        return $this->typeRules[$type];
    }
}
