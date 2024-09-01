<?php

namespace MalteKuhr\LaravelGpt\Enums;

enum ChatRole: string
{
    case ASSISTANT = 'assistant';
    case USER = 'user';

    /**
     * Get an array of all enum names.
     *
     * @return array
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'value');
    }
}
