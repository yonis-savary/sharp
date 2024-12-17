<?php

namespace YonisSavary\Sharp\Classes\Http\Classes;

abstract class HttpUtils
{
    /**
     * @return string Transformed header name to lower case
     * @example NULL `headerName('Content-Type') // returns 'content-type'`
     */
    protected function headerName(string $original): string
    {
        return trim(strtolower($original));
    }
}