<?php

declare(strict_types=1);

namespace Chiron\RoadRunner\Command;

use Chiron\Core\Directories;
use Chiron\Core\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

// TODO : utiliser symfony/process pour executer la ligne de commande plutot que l'instruction passthru() !!!

// TODO : utiliser ce bout de code pour détecter le binaire rr ???? https://github.com/jolicode/JoliNotif/blob/master/src/Notifier/CliBasedNotifier.php#L106

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
        $this->message('Roadrunner Server Starting...');
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
            return $directories->get('@root\bin\rr.exe');
        }

        return $directories->get('@root/bin/rr');
    }

    private static function isWindowsOs(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
}
