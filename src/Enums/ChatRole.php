<?php

namespace MalteKuhr\LaravelGPT\Enums;

enum ChatRole: string
{
    case ASSISTANT = 'assistant';
    case USER = 'user';
    case FUNCTION = 'function';
}
