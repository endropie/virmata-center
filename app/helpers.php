<?php
declare(strict_types=1);

if (! function_exists('stringable')) {
    function stringable($string = null)
    {
        return ($string == null) 
            ? new \Illuminate\Support\Str
            : \Illuminate\Support\Str::of($string);
    }
}