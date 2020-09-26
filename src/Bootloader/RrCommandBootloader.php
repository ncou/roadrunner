<?php

namespace Chiron\RoadRunner\Bootloader;

use Chiron\Core\Directories;
use Chiron\Bootload\AbstractBootloader;
use Chiron\PublishableCollection;
use Chiron\Console\Console;
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
