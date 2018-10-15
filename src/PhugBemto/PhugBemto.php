<?php

namespace PhugBemto;

use Phug\AbstractCompilerModule;
use Phug\Compiler;
use Phug\CompilerEvent;
use Phug\CompilerInterface;
use Phug\Renderer;
use Phug\Util\ModuleContainerInterface;

class PhugBemto extends AbstractCompilerModule
{
    public function __construct(ModuleContainerInterface $container)
    {
        parent::__construct($container);

        if ($container instanceof Renderer) {
            return;
        }

        /* @var Compiler $compiler */
        $compiler = $container;

        //Make sure we can retrieve the module options from the container
        $compiler->setOptionsRecursive([
            'module_options' => [
                'bemto' => [],
            ],
        ]);

        //Set default options
        $this->setOptionsRecursive([
            // example
        ]);

        //Apply options from container
        $this->setOptionsRecursive($compiler->getOption(['module_options', 'bemto']));

        $compiler->setOptionsRecursive([
            'patterns' => [
                'transform_expression' => function ($jsCode) use ($compiler) {
                    // example
                    return $jsCode;
                },
            ],
        ]);
    }

    /**
     * @return array
     */
    public function getEventListeners()
    {
        return [
            CompilerEvent::OUTPUT => function (Compiler\Event\OutputEvent $event) {
                /** @var CompilerInterface $compiler */
                $compiler = $event->getTarget();
                $output = $event->getOutput();
                // transform $output
                $event->setOutput($output);
            },
        ];
    }
}
