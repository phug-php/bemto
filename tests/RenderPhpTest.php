<?php

namespace Tests\Bemto;

class RenderPhpTest extends AbstractTestCase
{
    /**
     * @return \Generator
     */
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
        $this->assertSameHtml(
            $expectedOutput,
            $this->renderFile($sourceFile),
            'Unexpected output for ' . basename($sourceFile)
        );
    }
}
