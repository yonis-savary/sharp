<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\Web;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Web\Renderer;
use YonisSavary\Sharp\Core\Utils;

class RendererTest extends TestCase
{
    # Implicitly tested by test_render public function useTemplate(string $template, array $context=[]): void
    # Implicitly tested by test_render public function startSection(string $sectionName): void
    # Implicitly tested by test_render public function stopSection(): void
    # Implicitly tested by test_render public function section(string $sectionName): ?string

    public function test_findTemplate()
    {
        $r = Renderer::getInstance();

        $expectedPath = Utils::relativePath('/TestApp/Views/sharp-tests/sharp-tests-child.php');

        $this->assertEquals($expectedPath, $r->findTemplate('sharp-tests-child'));
        $this->assertEquals($expectedPath, $r->findTemplate('sharp-tests/sharp-tests-child'));
        $this->assertFalse($r->findTemplate('bad-subdir/sharp-tests-child'));
    }

    public function test_templateExists()
    {
        $r = Renderer::getInstance();

        $this->assertTrue($r->templateExists('sharp-tests-child'));
        $this->assertTrue($r->templateExists('sharp-tests/sharp-tests-child'));
        $this->assertFalse($r->templateExists('bad-subdir/sharp-tests-child'));
    }

    public function test_render()
    {
        $content = Renderer::getInstance()->render('sharp-tests-child', ['variable' => 'VARIABLE']);

        $this->assertTrue(substr_count($content, 'CHILD') == 1);
        $this->assertTrue(substr_count($content, 'PARENT') == 1);
        $this->assertTrue(substr_count($content, 'COMPONENT') == 2);
        $this->assertTrue(substr_count($content, 'VARIABLE') == 1);
    }
}