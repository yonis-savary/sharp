<?php

namespace YonisSavary\Sharp\Classes\Extras\AutobahnDrivers;

use YonisSavary\Sharp\Classes\Data\Database;
use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Http\Response;

interface DriverInterface
{
    public function __construct(Database $database=null);
    public function createCallback(Request $request): Response;
    public function multipleCreateCallback(Request $request): Response;
    public function readCallback(Request $request): Response;
    public function updateCallback(Request $request): Response;
    public function deleteCallback(Request $request): Response;
}