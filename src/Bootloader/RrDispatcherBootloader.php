<?php

declare(strict_types=1);

namespace Chiron\RoadRunner\Bootloader;

use Chiron\Application;
use Chiron\Bootload\AbstractBootloader;
use Chiron\Config\AppConfig;
use Chiron\Container\FactoryInterface;
use Chiron\Dispatcher\ConsoleDispatcher;
use Chiron\Dispatcher\SapiDispatcher;
use Chiron\RoadRunner\RrDispatcher;


final class RrDispatcherBootloader extends AbstractBootloader
{
    public function boot(Application $application, FactoryInterface $factory): void
    {
        $application->addDispatcher($factory->build(RrDispatcher::class));
    }
}
