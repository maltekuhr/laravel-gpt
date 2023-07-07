<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;

;

class UrlRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        if (in_array('url', $this->rules) || in_array('active_url', $this->rules)) {
            $this->setType('string');
            $this->addDescription('The field must be a valid URL.');
        }
    }
}