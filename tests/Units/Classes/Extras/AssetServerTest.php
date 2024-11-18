<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\Extras;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Extras\AssetServer;
use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Http\Response;
use YonisSavary\Sharp\Core\Utils;

class AssetServerTest extends TestCase
{
    protected function getNewAssetServer(): AssetServer
    {
        return new AssetServer();
    }

    public function test_findAsset()
    {
        $s = $this->getNewAssetServer();

        $script = Utils::relativePath('/TestApp/Assets/sharp-tests/sharp-tests-script.js');
        $style = Utils::relativePath('/TestApp/Assets/sharp-tests/sharp-tests-style.css');

        $this->assertEquals($script, $s->findAsset('sharp-tests-script.js'));
        $this->assertEquals($style, $s->findAsset('sharp-tests-style.css'));
        $this->assertEquals($script, $s->findAsset('sharp-tests/sharp-tests-script.js'));
        $this->assertEquals($style, $s->findAsset('sharp-tests/sharp-tests-style.css'));

        $this->assertFalse($s->findAsset('sharp-tests-script.css'));
        $this->assertFalse($s->findAsset('sharp-tests-style.js'));
    }

    public function test_getURL()
    {
        $s = $this->getNewAssetServer();

        $this->assertEquals('/assets/file.js', $s->getURL('file.js'));
        $this->assertEquals('/assets/directory/style.css', $s->getURL('directory/style.css'));
    }

    public function test_handleRequest()
    {
        $s = $this->getNewAssetServer();

        $res = $s->handleRequest(
            new Request('GET', '/assets/sharp-tests-script.js'),
            true
        );
        $this->assertInstanceOf(Response::class, $res);
    }
}