<?php

namespace Tests\Bemto;

use PHPUnit\Framework\TestCase;
use Phug\Renderer;
use PhugBemto\PhugBemto;
use XhtmlFormatter\Formatter;

abstract class AbstractTestCase extends TestCase
{
    /**
     * @var array
     */
    protected $modules = [];

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @throws \Phug\RendererException
     */
    protected function getRenderer(): Renderer
    {
        if (!isset($this->renderer)) {
            $this->renderer = new Renderer([
                'debug'              => true,
                'execution_max_time' => 180000,
                'modules'            => array_merge([
                    PhugBemto::class,
                ], $this->modules),
            ]);
        }

        return $this->renderer;
    }

    protected function renderFile($sourceFile)
    {
        $error = null;
        $actualOutput = null;

        try {
            $actualOutput = $this->getRenderer()->renderFile($sourceFile);
        } catch (\Throwable $exception) {
            $error = $exception;

            try {
                $debugFile = __DIR__ . '/../debug.php';
                file_put_contents($debugFile, $this->getRenderer()->compileFile($sourceFile));
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
        $formatter = new Formatter();

        $html = trim($formatter->format($html));
        $html = str_replace("\r", '', preg_replace('/\s+$/m', '', $html));
        $html = preg_replace('/\n{2,}$/', "\n", $html);
        $html = preg_replace('/<!--\s*(\S(.*\S)?)\s*-->/U', '<!-- $1 -->', $html);
        $html = preg_replace('/(<[a-z][^>]*>)\s+<\//', '$1</', $html);
        $html = preg_replace('/([a-z"\'])\/>/', '$1 />', $html);
        $html = preg_replace('/(<[a-z][^>]*)\s\/>/', '$1>', $html);

        if (version_compare(PHP_VERSION, '8.1.0-dev', '>=')) {
            $html = str_replace('&#039;', "'", $html);
        }

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
