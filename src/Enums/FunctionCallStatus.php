<?php

namespace MalteKuhr\LaravelGpt\Enums;

enum FunctionCallStatus: string
{
    case NEW = 'new';
    case UNAPPROVED = 'unapproved';
    case PENDING = 'pending';
    case ERROR = 'error';
    case COMPLETED = 'completed';
}
