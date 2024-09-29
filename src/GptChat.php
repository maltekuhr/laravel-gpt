<?php

namespace MalteKuhr\LaravelGpt;

use MalteKuhr\LaravelGpt\Concerns\HasGptChat;
use MalteKuhr\LaravelGpt\Helper\Dir;
use MalteKuhr\LaravelGpt\Contracts\BaseChat;

abstract class GptChat extends BaseChat
{
    use Dir;
}