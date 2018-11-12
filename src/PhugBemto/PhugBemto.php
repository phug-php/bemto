<?php

namespace PhugBemto;

use Phug\AbstractCompilerModule;
use Phug\Util\ModuleContainerInterface;

class PhugBemto extends AbstractCompilerModule
{
    public function __construct(ModuleContainerInterface $container)
    {
        parent::__construct($container);

        $file = realpath(__DIR__.'/../../bemto.pug');
        $includes = $container->getOption('includes');

        if (!in_array($file, $includes)) {
            $includes[] = $file;
            $container->setOption('includes', $includes);
        }
    }
}
