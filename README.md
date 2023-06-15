LaravelGPT
---
[![Latest Version on Packagist](https://img.shields.io/packagist/v/maltekuhr/laravel-gpt.svg?style=flat-square)](https://packagist.org/packages/maltekuhr/laravel-gpt)
[![Total Downloads](https://img.shields.io/packagist/dt/maltekuhr/laravel-gpt.svg?style=flat-square)](https://packagist.org/packages/maltekuhr/laravel-gpt)

This package makes it easy to work with the OpenAI Chat Completion API including Function Calling. The package can translate Laravel Validation Rules into the JSON Schema format that is required by the API.

## Example
```php
class SentimentGPTRequest extends GPTRequest
{
    public function __construct(
        public Customer $customer
    ) {}

    public function systemMessage(): ?string
    {
        return BladePromptService::render(__DIR__, 'system');
    }

    public function functions(): ?array
    {
        return [
            new SaveSentimentGPTFunction($this->customer)
        ];
    }

    public function functionCall(): string|bool|null
    {
        return SaveSentimentGPTFunction::class;
    }

    public function model(): string
    {
        return 'gpt-3.5-turbo';
    }
}

```
```php
class SaveSentimentGPTFunction extends GPTFunction
{
    public function __construct(
        public Customer $customer
    ) {}
    
    public function function(): Closure
    {
        return function (string $sentiment): void {
            $this->customer->sentiment = $sentiment;
            $this->customer->save();
        };
    }

    public function rules(): array
    {
        return [
            'sentiment' => 'required|string|in:POSITIVE,NEGATIVE,NEUTRAL',
        ];
    }

    public function description(): string
    {
        return 'Saves the sentiment of a message.';
    }
}

```
### Usage
```php
echo $customer->sentiment; // null

SentimentGPTRequest::make($customer)
    ->addMessage("I love this product!")
    ->send();

echo $customer->sentiment; // POSITIVE
```

## Installation
You can install the package via composer:

```bash
composer require maltekuhr/laravel-gpt
```

Next you need to configure your OpenAI API Key and Organization ID. You can find both in the [OpenAI Dashboard](https://platform.openai.com/account/org-settings).

```dotenv
OPENAI_ORGANIZATION=YOUR_ORGANIZATION_ID
OPENAI_API_KEY=YOUR_API_KEY
```

## Further Documentation
For further documentation please see the [Wiki](https://github.com/maltekuhr/laravel-gpt/wiki).

### ToDos
 - Tests for the core and for the validation rule converters
 - Support for all Laravel Validation Rules
 - Better documentation

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email malte@maltekuhr.de instead of using the issue tracker.

## Credits

- [Malte Kuhr](https://github.com/maltekuhr)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.