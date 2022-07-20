<?php

declare(strict_types=1);

namespace Chiron\RoadRunner\Bootloader;

use Chiron\Console\Console;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\RoadRunner\Command\RrInstallCommand;
use Chiron\RoadRunner\Command\RrServeCommand;

final class RrCommandBootloader extends AbstractBootloader
{
    public function boot(Console $console): void
    {
        $console->addCommand(RrInstallCommand::getDefaultName(), RrInstallCommand::class);
        $console->addCommand(RrServeCommand::getDefaultName(), RrServeCommand::class);
    }
}
