<?php

namespace MalteKuhr\LaravelGPT;

use Closure;
use MalteKuhr\LaravelGPT\Managers\FunctionManager;

abstract class GPTFunction
{
    /**
     * Provides the name of the GPTFunction to be used for function calls. Ensure
     * to provide a unique name within the functions() array in any Request where
     * this function is used.
     *
     * @return string
     */
    public function name(): string
    {
        return FunctionManager::getFunctionName($this);
    }

    /**
     * Describes the purpose and functionality of the GPT function. This is utilized
     * for generating the function documentation.
     *
     * @return string
     */
    abstract public function description(): string;

    /**
     * Defines the rules for input validation and JSON schema generation. Override this
     * method to provide custom validation rules for the function. The documentation will
     * have the same order as the rules are defined in this method.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Specifies the error messages to be used with the Validator. If an empty array is
     * returned, the default Laravel error messages are utilized. Note: Use Laravel syntax
     * to define error messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Specifies a function to be invoked by the model. The function is implemented as a
     * Closure which may take parameters that are provided by the model. If extra arguments
     * are included in the documentation to optimize model's performance (by allowing it more
     * thinking time), these can be disregarded by not including them within the Closure
     * parameters.
     *
     * If the Closure returns null, the chat interaction is paused until the 'send()' method in
     * the request is invoked again. For all other return values, the response is JSON encoded
     * and forwarded to the model for further processing.
     *
     * @return Closure
     */
    abstract public function function(): Closure;
}