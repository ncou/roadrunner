<?php

declare(strict_types=1);

namespace Chiron\RoadRunner\Command;

use Chiron\Core\Directories;
use Chiron\Core\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Chiron\Filesystem\Filesystem;

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

        $source = __DIR__ . '/../../config/.rr.yaml.dist';
        $destination = $directories->get('@root/.rr.yaml');
        // copy and overwrite existing rr config file.
        $filesystem->copy($source, $destination);

        $this->info('File ".rr.yaml" to : ' . $destination);
    }

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
