<?php

namespace Tests\Bemto;

use JsPhpize\JsPhpizePhug;

class RenderJsTest extends AbstractTestCase
{
    /**
     * @var array
     */
    protected $modules = [
        JsPhpizePhug::class,
    ];

    /**
     * @return \Generator
     */
    public function getCases()
    {
        foreach (glob(__DIR__ . '/cases/*.html') as $file) {
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
        $this->assertSameHtml(
            $expectedOutput,
            $this->renderFile($sourceFile),
            'Unexpected output for ' . basename($sourceFile)
        );
    }
}
