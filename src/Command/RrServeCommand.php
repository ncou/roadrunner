<?php

declare(strict_types=1);

namespace Chiron\RoadRunner\Command;

use Chiron\Core\Command\AbstractCommand;
use Chiron\Core\Directories;
use Chiron\WebServer\Exception\WebServerException;
use Chiron\WebServer\Traits\WebAdressAvailableTrait;
use Chiron\WebServer\Traits\WebServerTrait;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

//https://github.com/yiisoft/yii-console/blob/master/src/Command/Serve.php#L106
//https://github.com/symfony/web-server-bundle/blob/4.4/Command/ServerStatusCommand.php
//https://github.com/symfony/web-server-bundle/blob/4.4/Command/ServerStopCommand.php

//https://github.com/Baldinof/roadrunner-bundle/blob/2.x/src/Command/WorkerCommand.php

//https://github.com/symfony/panther/blob/4c22ea19c316590b1fb2959b8fb330aa2e179714/src/ProcessManager/WebServerReadinessProbeTrait.php#L44

// https://medium.com/@SlyFireFox/laravel-octane-running-roadrunner-directly-with-rr-yaml-378a317f8579
//https://github.com/laravel/octane/blob/ea1f118e0736993b25861a3cd5ad623d52bbc220/src/Commands/StartRoadRunnerCommand.php
//https://github.com/laravel/octane/tree/1.x/src/Commands

//https://github.com/selfinvoking/laravel-rr/blob/master/app/Console/Commands/RoadRunnerCommand.php
//https://github.com/spiral/roadrunner-laravel/blob/master/src/Console/Commands/StartCommand.php

// TODO : utiliser symfony/process pour executer la ligne de commande plutot que l'instruction passthru() !!!

// TODO : utiliser ce bout de code pour détecter le binaire rr ???? https://github.com/jolicode/JoliNotif/blob/master/src/Notifier/CliBasedNotifier.php#L106

// chokidar pour vérifier les fichiers qui ont bougés :
// https://github.com/laravel/octane/blob/1.x/src/Commands/Concerns/InteractsWithServers.php
// https://github.com/spatie/file-system-watcher/blob/main/src/Watch.php#L140

final class RrServeCommand extends AbstractCommand
{
    use WebServerTrait;

    protected static $defaultName = 'rr:serve';

    public function configure(): void
    {
        $this
            ->setDescription('Runs RoadRunner server.')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'debug mode');
    }

    public function perform(Directories $directories)
    {
        try {
            $server = $this->createServerProcess($directories);

            $this->message('Roadrunner server running…');
            $this->info('Quit the server with CTRL-C or COMMAND-C.');

            $this->runServer($server);
        } catch (WebServerException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    //https://github.com/laravel/octane/blob/1.x/src/RoadRunner/Concerns/FindsRoadRunnerBinary.php#L21
    protected function createServerProcess(Directories $directories): Process
    {
        //$rrBinary = $this->getRrBinary($directories);

        // Locate the Roadrunner Binary path.
        $finder = new ExecutableFinder();
        $rrBinary = $finder->find('rr', null, [$directories->get('@root/bin/')]);

        if ($rrBinary === null) {
            throw new WebServerException('Unable to find the Roadrunner binary.');
        }

        // the default config file used is the ".rr.yaml" file presents at the root directory.
        /*
        $commandLine = sprintf('"%s" serve', $rrBinary);

        if ($this->option('debug')) {
            $commandLine .= ' -d';
        }
        if ($this->isVerbose()) {
            $commandLine .= ' -v';
        }*/

        // TODO : utiliser les flags -v et -d dans la ligne de commande !!!!
        // TODO : vérifier l'utilité du array_filter !!!!
        $process = new Process(array_filter([
            $rrBinary,
            '-o',
            'http.address=localhost:8080',
            '-o',
            'rpc.listen=tcp://localhost:6081',
            '-o',
            'logs.mode=production',
            '-o',
            'logs.level=debug',
            '-o',
            'logs.output=stdout',
            '-o',
            'logs.encoding=json',
            'serve',
        ]));

        $process->setTimeout(null);

        return $process;
    }
}
