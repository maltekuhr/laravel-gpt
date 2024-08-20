<?php

namespace MalteKuhr\LaravelGPT\Enums;



enum ChatStatus: string
{
    case IDLE = 'idle';
    case RUNNING = 'running';

    public static function names(): array
    {
        return array_column(static::cases(), 'name');
    }
}
