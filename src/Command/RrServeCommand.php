<?php

namespace Chiron\RoadRunner\Command;

use Chiron\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Chiron\Core\Directories;

final class RrServeCommand extends AbstractCommand
{
    protected static $defaultName = 'rr:serve';

    public function configure(): void
    {
        $this
            ->setDescription('Runs RoadRunner server.')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'debug mode');
    }

    public function perform(Directories $directories)
    {
        $this->message("Roadrunner Server Starting...");
        $this->info('Quit the server with CTRL-C or COMMAND-C.');

        $rrBinary = $this->getRrBinary($directories);
        // the default config file used is the ".rr.yaml" file presents at the root directory.
        $commandLine = sprintf('"%s" serve', $rrBinary);

        if ($this->option('debug')) {
            $commandLine .= ' -d';
        }
        if ($this->isVerbose()) {
            $commandLine .= ' -v';
        }

        passthru($commandLine);
    }

    private function getRrBinary(Directories $directories): string
    {
        if (self::isWindowsOs()) {
            return $directories->get("@root\\bin\\rr.exe");
        }

        return $directories->get("@root/bin/rr");
    }

    private static function isWindowsOs(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
}
