<?php

namespace MalteKuhr\LaravelGPT\Enums;

enum ChatType: string
{
    case CHAT = 'chat';
    case ACTION = 'action';

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
