<?php

namespace MalteKuhr\LaravelGpt\Facades;

use Illuminate\Support\Facades\Facade;
use MalteKuhr\LaravelGpt\Services\JsonPathFinderService\JsonPathFinderService as JsonPathFinderServiceClass;

/**
 * @method static array|null findPosition(string $rawJson, string $path)
 *
 * @see JsonPathFinderServiceClass
 */
class JsonPathFinder extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return JsonPathFinderServiceClass::class;
    }
}