<?php

namespace Tests\Bemto;

class RenderTest extends AbstractTestCase
{
    public function getCases()
    {
        foreach (glob(__DIR__ . '/cases/1_basics.html') as $file) {
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
        $this->assertSameHtml($expectedOutput, $this->renderFile($sourceFile));
    }
}