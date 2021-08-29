<?php

declare(strict_types=1);

namespace Chiron\RoadRunner\Bootloader;

use Chiron\Application;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Injector\FactoryInterface;
use Chiron\RoadRunner\RrEngine;

final class RrEngineBootloader extends AbstractBootloader
{
    public function boot(Application $application, FactoryInterface $factory): void
    {
        $application->addEngine($factory->build(RrEngine::class));
    }
}
