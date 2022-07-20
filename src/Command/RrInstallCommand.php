<?php

declare(strict_types=1);

namespace Chiron\RoadRunner\Command;

use Chiron\Core\Command\AbstractCommand;
use Chiron\Core\Directories;
use Chiron\Filesystem\Filesystem;

// TODO : utiliser symfony/process pour executer la ligne de commande plutot que l'instruction passthru() !!!

// TODO : utiliser ce bout de code pour détecter le binaire rr ???? https://github.com/jolicode/JoliNotif/blob/master/src/Notifier/CliBasedNotifier.php#L106

final class RrInstallCommand extends AbstractCommand
{
    protected static $defaultName = 'rr:install';

    public function configure(): void
    {
        $this->setDescription('Install RoadRunner Binary.');
    }

    public function perform(Filesystem $filesystem, Directories $directories)
    {
        $this->copyRrConfigFile($filesystem, $directories);
        $this->downloadRrBinary($directories);

        $this->success('Installation successful!');
    }

    private function copyRrConfigFile(Filesystem $filesystem, Directories $directories): void
    {
        $this->message('Copy Roadrunner configuration file...');

        $source = __DIR__ . '/../../resources/.rr.yaml.dist';
        $destination = $directories->get('@root/.rr.yaml');
        // copy and overwrite existing rr config file.
        $filesystem->copy($source, $destination);

        $this->info('File ".rr.yaml" to : ' . $destination);
    }

    //https://github.com/laravel/octane/blob/612544b429c01e31a8b2480eb6e08a36f946dd88/src/Commands/Concerns/InstallsRoadRunnerDependencies.php#L162
    private function downloadRrBinary(Directories $directories): void
    {
        $this->message('Download Roadrunner server binary...');

        $commandLine = sprintf(
            '"%s" get-binary --location "%s"',
            $directories->get('@vendor/bin/rr'),
            $directories->get('@root/bin')
        );

        passthru($commandLine);
    }
}
