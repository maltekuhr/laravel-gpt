![LaravelGPT](https://github.com/maltekuhr/laravel-gpt/assets/80050109/21071831-78ba-4d0a-9911-f19f5a174e0b)
# Unleash the Power of OpenAI's ChatGPT API
[![Latest Version on Packagist](https://img.shields.io/packagist/v/maltekuhr/laravel-gpt.svg?style=flat-square)](https://packagist.org/packages/maltekuhr/laravel-gpt)
[![Total Downloads](https://img.shields.io/packagist/dt/maltekuhr/laravel-gpt.svg?style=flat-square)](https://packagist.org/packages/maltekuhr/laravel-gpt)

Introducing LaravelGPT, a tool designed to simplify the integration of OpenAI's ChatGPT with your Laravel applications. This package offers a seamless connection to the OpenAI Chat Completions API, even supporting Function Calling. Forget the complications of crafting the correct JSON schema - LaravelGPT does the work for you. Say goodbye to dense, hard-to-read code, and start building the applications of the future! With LaravelGPT, you can truly unleash the power of ChatGPT in your applications, setting the stage for innovation and advanced user interaction.

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

You are now ready to use LaravelGPT! I recommend you to read the [Wiki](https://github.com/maltekuhr/laravel-gpt/wiki).

### Publishing the Config File
You can publish the config file with:
```bash
php artisan vendor:publish --provider="MalteKuhr\LaravelGPT\Providers\GPTServiceProvider" --tag="config"
```

## Core Concept
LaravelGPT provides structured pathways to incorporate GPT into your project. It introduces two key components: `GPTAction` and `GPTChat`.

### GPTAction
`GPTAction` is designed for simple scenarios where you have a single input (like a Customer Review) and a single task to perform (like determining sentiment). This is not intended for conversation-like interactions but for straightforward tasks.

### GPTChat
On the other hand, `GPTChat` can handle multiple functions and is useful for conversational scenarios. It is important to note that `GPTAction` is built on top of `GPTChat`. Do not mix up `GPTAction` with `GPTFunction`. The latter is used within `GPTChat` to supply the model with callable functions. In contrast, `GPTAction` is leveraged to provide a streamlined interface for use cases where you have a single input and a single task to accomplish.

## Example
In the following examples we are using LaravelGPT to determine the sentiment of a customer review. Both examples are delivering the same result, but the `GPTChat` example is more complex and provides more flexibility.

### Using `GPTAction`
You can create a new `GPTAction` class by running the following command. You can add the `--clean` option to remove the explanatory comments.
```bash
php artisan make:gpt-action Sentiment
```

```php
<?php

namespace App\GPT\Actions\Sentiment;

use MalteKuhr\LaravelGPT\GPTAction;
use Closure;

class SentimentGPTAction extends GPTAction
{
    public function __construct(
        protected Customer $customer,
    ) {}

    public function systemMessage(): ?string
    {
        return 'Determine the sentiment of the customer review.';
    }

    public function function(): Closure
    {
        return function (string $sentiment): mixed {
            $this->customer->sentiment = $sentiment;
            $this->customer->save();
            
            return [
                'sentiment' => $sentiment
            ];
        };
    }

    public function rules(): array
    {
        return [
            'sentiment' => 'required|string|in:POSITIVE,NEGATIVE,NEUTRAL'
        ];
    }
}
```

#### Using the `SentimentGPTAction` Class
```php
echo $customer->sentiment; // null

echo SentimentGPTAction::make($customer)->send('I really like this product.'); // ['sentiment' => 'POSITIVE']

echo $customer->sentiment; // POSITIVE
```


### Using `GPTChat`
This example leverages the GPT-3.5 to determine the sentiment for a message received by the customer. The example provides a walkthrough for creating a `SentimentGPTChat` class and the associated `SaveSentimentGPTFunction`.

#### Creating the `SentimentGPTChat` Class
You can create a new `GPTChat` class by running the following command. You can add the `--clean` option to remove the explanatory comments.
```bash
php artisan make:gpt-chat Sentiment
```

It is recommended to create a GPTChat for each use case. The `SentimentGPTChat` will instruct the model to evaluate the sentiment of a message, then forces the model to respond with a function call.

```php
class SentimentGPTChat extends GPTChat
{
    public function __construct(public Customer $customer) {}

    public function systemMessage(): ?string
    {
        // use the BladePromptService or a simple string if you want to use the
        // BladePromptService, you need to create a blade file in the directory
        return BladePromptService::render(__DIR__, 'system', [
            'customer' => $this->customer,
        ]);
    }

    public function functions(): ?array
    {
        // Defines the set of functions accessible to the model. You
        // don't need to remove a function from this array if you force
        // the model to call another function. The package will handle
        // that for you.
        return [
            new SaveSentimentGPTFunction($this->customer)
        ];
    }

    public function functionCall(): string|bool|null
    {
        // If returned null, the model autonomously decides on calling a function.
        // A false return compels the model to respond with a message, while providing
        // a function name obligates the model to invoke that function.
        
        return SaveSentimentGPTFunction::class;
    }
}
```

#### Create the `SaveSentimentGPTFunction` Class
You can create a new `GPTFunction` class by running the following command. You can add the `--clean` option to remove the explanatory comments.
```bash
php artisan make:gpt-function SaveSentiment
```


```php
class SaveSentimentGPTFunction extends GPTFunction
{
    public function __construct(public Customer $customer) {}
    
    public function function(): Closure
    {
        // The model calls this function with parameters that align with those in
        // the 'rules' method. The 'rules' method may have additional parameters,
        // but the function must not exceed the parameters defined in 'rules'.
        
        return function (string $sentiment): void {
            $this->customer->sentiment = $sentiment;
            $this->customer->save();
        };
    }

    public function rules(): array
    {
        // The 'rules' serve dual purpose: validating the function call and generating
        // its documentation for the model. You can add support for custom validation
        // rules by extending the RuleConverter class and adding the converter to the
        // laravel-gpt config file.
        return [
            'sentiment' => 'required|string|in:POSITIVE,NEGATIVE,NEUTRAL',
        ];
    }

    public function description(): string
    {
        // This is the description utilized in the documentation.
        return 'Saves the sentiment of a message.';
    }
}
```

#### Using the `SentimentGPTChat` Class
To use the `SentimentGPTChat` you just need to `make()` an new instance of the class, add your message, and send the request.

```php
echo $customer->sentiment; // null

SentimentGPTChat::make($customer)->addMessage("I love this product!")->send();

echo $customer->sentiment; // POSITIVE
```

This example demonstrates how you can easily analyze sentiment and save it directly into your customer model using LaravelGPT.

## Further Documentation
For further documentation please see the [Wiki](https://github.com/maltekuhr/laravel-gpt/wiki).

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email malte@maltekuhr.de instead of using the issue tracker.

## Credits

- [Malte Kuhr](https://github.com/maltekuhr)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.