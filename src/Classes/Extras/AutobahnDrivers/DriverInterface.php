<?php

namespace YonisSavary\Sharp\Classes\Extras\AutobahnDrivers;

use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Http\Response;

interface DriverInterface
{
    public static function createCallback(Request $request): Response;
    public static function multipleCreateCallback(Request $request): Response;
    public static function readCallback(Request $request): Response;
    public static function updateCallback(Request $request): Response;
    public static function deleteCallback(Request $request): Response;
}