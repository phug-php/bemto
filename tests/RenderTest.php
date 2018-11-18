<?php

namespace Tests\Bemto;

use JsPhpize\JsPhpizePhug;
use Phug\Renderer;
use PhugBemto\PhugBemto;

class RenderTest extends AbstractTestCase
{
    public function getJsCases()
    {
        foreach (glob(__DIR__ . '/cases/*.html') as $file) {
            yield [
                file_get_contents($file),
                preg_replace('/\.html$/', '.pug', $file),
            ];
        }
    }

    public function getCases()
    {
        foreach (glob(__DIR__ . '/cases-php/*.html') as $file) {
            yield [
                file_get_contents($file),
                preg_replace('/\.html$/', '.pug', $file),
            ];
        }
    }

    /**
     * @dataProvider getCases
     */
    public function testRender($expectedOutput, $sourceFile)
    {
        $this->assertSameHtml($expectedOutput, $this->renderFile($sourceFile), 'Unexpected output for ' . basename($sourceFile));
    }

    /**
     * @dataProvider getJsCases
     */
    public function testRenderJs($expectedOutput, $sourceFile)
    {
        $this->renderer = new Renderer([
            'debug' => true,
            'execution_max_time' => 180000,
            'modules' => [
                JsPhpizePhug::class,
                PhugBemto::class,
            ],
        ]);
        $this->assertSameHtml($expectedOutput, $this->renderFile($sourceFile), 'Unexpected output for ' . basename($sourceFile));
    }
}