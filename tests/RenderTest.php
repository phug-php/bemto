<?php

namespace Tests\Bemto;

use PHPUnit\Framework\TestCase;
use Phug\Renderer;
use PhugBemto\PhugBemto;

class RenderTest extends TestCase
{
    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @throws \Phug\RendererException
     */
    protected function setUp()
    {
        parent::setUp();
        $this->renderer = new Renderer([
            'debug' => true,
            'execution_max_time' => 5000,
            'modules' => [
                PhugBemto::class,
            ],
        ]);
    }

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
        try {
            $this->assertSame($expectedOutput, $this->renderer->renderFile($sourceFile));
        } catch (\Throwable $exception) {
            try {
                $debugFile = 'debug.php';
                file_put_contents($debugFile, $this->renderer->compileFile($sourceFile));
                include $debugFile;
            } catch (\Throwable $exception) {
                throw new \Exception('Error in ' . $sourceFile . "\n" . $exception->getMessage(), 0, $exception);
            }
        }
    }
}