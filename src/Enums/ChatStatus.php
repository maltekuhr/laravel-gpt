<?php

namespace MalteKuhr\LaravelGpt\Enums;



enum ChatStatus: string
{
    case IDLE = 'idle';
    case RUNNING = 'running';

    public static function names(): array
    {
        return array_column(static::cases(), 'value');
    }
}
