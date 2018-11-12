<?php

namespace Tests\Bemto;

use Mihaeu\HtmlFormatter;
use PHPUnit\Framework\TestCase;
use Phug\Renderer;
use PhugBemto\PhugBemto;

abstract class AbstractTestCase extends TestCase
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
            'execution_max_time' => 180000,
            'modules' => [
                PhugBemto::class,
            ],
        ]);
    }

    protected function renderFile($sourceFile)
    {
        $error = null;
        $actualOutput = null;
        try {
            $actualOutput = $this->renderer->renderFile($sourceFile);
        } catch (\Throwable $exception) {
            $error = $exception;
            try {
                $debugFile = 'debug.php';
                file_put_contents($debugFile, $this->renderer->compileFile($sourceFile));
                include $debugFile;
            } catch (\Throwable $exception) {
                throw new \Exception('Error in ' . $sourceFile . "\n" . $exception->getMessage(), 0, $exception);
            }
        }
        if ($error) {
            throw $error;
        }

        return $actualOutput;
    }

    protected static function htmlStandardize($html)
    {
        $html = trim(HtmlFormatter::format($html));
        $html = str_replace("\r", '', preg_replace('/\s+$/m', '', $html));
        $html = preg_replace('/\n{2,}$/', "\n", $html);
        $html = preg_replace('/<!--\s*(\S(.*\S)?)\s*-->/U', '<!-- $1 -->', $html);
        $html = preg_replace('/(<[^\/>]+>)\s+<\//', '$1</', $html);
        $html = preg_replace('/([a-z"\'])\/>/', '$1 />', $html);

        return $html;
    }

    protected function assertSameHtml($expectedOutput, $actualOutput, $message = null)
    {
        $this->assertSame(
            static::htmlStandardize($expectedOutput),
            static::htmlStandardize($actualOutput),
            $message ?: 'HTML should render the same string once formatted.'
        );
    }
}