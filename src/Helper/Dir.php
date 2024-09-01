<?php

namespace MalteKuhr\LaravelGpt\Helper;

trait Dir
{
    /**
     * Returns the directory of the current file.
     *
     * @return string
     */
    public static function getDir(): string
    {
        return __DIR__;
    }
}