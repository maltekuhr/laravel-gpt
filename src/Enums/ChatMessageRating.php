<?php

namespace MalteKuhr\LaravelGPT\Enums;

enum ChatRole: string
{
    case POSITIVE = 'positive';
    case NEGATIVE = 'negative';

    /**
     * Get an array of all enum names.
     *
     * @return array
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}
