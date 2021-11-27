<?php

declare(strict_types=1);

namespace Chiron\RoadRunner\Command;

use Chiron\Core\Directories;
use Chiron\Core\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

use Symfony\Component\Process\ExecutableFinder;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

//https://github.com/yiisoft/yii-console/blob/master/src/Command/Serve.php#L106
//https://github.com/symfony/web-server-bundle/blob/4.4/Command/ServerStatusCommand.php
//https://github.com/symfony/web-server-bundle/blob/4.4/Command/ServerStopCommand.php

//https://github.com/Baldinof/roadrunner-bundle/blob/2.x/src/Command/WorkerCommand.php

//https://github.com/laravel/octane/blob/ea1f118e0736993b25861a3cd5ad623d52bbc220/src/Commands/StartRoadRunnerCommand.php
//https://github.com/laravel/octane/tree/1.x/src/Commands

//https://github.com/selfinvoking/laravel-rr/blob/master/app/Console/Commands/RoadRunnerCommand.php
//https://github.com/spiral/roadrunner-laravel/blob/master/src/Console/Commands/StartCommand.php

// TODO : utiliser symfony/process pour executer la ligne de commande plutot que l'instruction passthru() !!!

// TODO : utiliser ce bout de code pour détecter le binaire rr ???? https://github.com/jolicode/JoliNotif/blob/master/src/Notifier/CliBasedNotifier.php#L106

final class RrServeCommand extends AbstractCommand
{
    protected static $defaultName = 'rr:serve';

    private $directories;

    public function configure(): void
    {
        $this
            ->setDescription('Runs RoadRunner server.')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'debug mode');
    }

    public function perform(Directories $directories)
    {
        $this->directories = $directories;

        $this->message('Roadrunner Server Starting...');

        $process = $this->createServerProcess();
        $process->start();

        $this->info('Quit the server with CTRL-C or COMMAND-C.');

        $this->runServer2($process);
    }




    public function perform_OLD2(Directories $directories)
    {
        $this->directories = $directories;

        $this->message('Roadrunner Server Starting...');
        $this->info('Quit the server with CTRL-C or COMMAND-C.');

        $this->runServer();
    }

    public function perform_OLD(Directories $directories)
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

    //https://github.com/laravel/octane/blob/1.x/src/RoadRunner/Concerns/FindsRoadRunnerBinary.php#L21
    private function getRrBinary(Directories $directories): string
    {
        // TODO : faire plutot un ExecutableFinder('rr') ca évitera de vérifier l'OS !!!!
        if (self::isWindowsOs()) {
            return $directories->get('@root\bin\rr.exe');
        }

        return $directories->get('@root/bin/rr');
    }

    // TODO : Mettre cette fonction dans une classe dans le package chiron/support sous le nom System::class ou OperatingSystem::class
    // https://github.com/tivie/php-os-detector/blob/master/src/Detector.php
    private static function isWindowsOs(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }




    protected function createServerProcess(): Process
    {
        $rrBinary = $this->getRrBinary($this->directories);

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
        $process = new Process(array_filter([
            $rrBinary,
            '-o', 'http.address=localhost:8080',
            '-o', 'rpc.listen=tcp://localhost:6081',
            '-o', 'logs.mode=production',
            '-o', 'logs.level=debug',
            '-o', 'logs.output=stdout',
            '-o', 'logs.encoding=json',
            'serve'
            ]
        ));

        $process->setTimeout(null);

        return $process;
    }





    /**
     * Run the given server process.
     *
     * @param  \Symfony\Component\Process\Process  $server
     *
     * @return int
     */
    protected function runServer2($server)
    {
        while (! $server->isStarted()) {
            sleep(1);
        }

        //$this->writeServerRunningMessage();

        //$watcher = $this->startServerWatcher();

        try {
            while ($server->isRunning()) {
                $this->writeServerOutput($server);

/*
                if ($watcher->isRunning() &&
                    $watcher->getIncrementalOutput()) {
                    $this->info('Application change detected. Restarting workers…');

                    $inspector->reloadServer();
                } elseif ($watcher->isTerminated()) {
                    $this->error(
                        'Watcher process has terminated. Please ensure Node and chokidar are installed.'.PHP_EOL.
                        $watcher->getErrorOutput()
                    );

                    return 1;
                }
*/

                usleep(500 * 1000);
            }

            $this->writeServerOutput($server);
        } catch (ServerShutdownException $e) {
           return 1;
        } finally {
            //$this->stopServer();
        }

        return $server->getExitCode();
    }


    /**
     * Write the server process output to the console.
     *
     * @param  \Symfony\Component\Process\Process  $server
     * @return void
     */
    // https://github.com/laravel/octane/blob/ea1f118e0736993b25861a3cd5ad623d52bbc220/src/Commands/StartRoadRunnerCommand.php#L183
    protected function writeServerOutput($server)
    {
        $output = $server->getIncrementalOutput();
        if ($output !== '') {
            $this->info($output);
        }

        $ouput = $server->getIncrementalErrorOutput();
        if ($output !== '') {
            $this->error($output);
        }
    }











    public function runServer(): void
    {


        // Ensure the server adress is not already taken.
        //$this->assertAdressAvailable($this->hostname, $this->port); // TODO : verification à réactiver !!!!


        // Prepare the server command line to execute.
        $process = $this->createServerProcess();
        $callback = $this->getOutputCallback();

        // Quiet mode (will unset the output callback).
        if ($this->output->isQuiet()) {
            $process->disableOutput();
            $callback = null;
        }
        // Execute the command line and block the console.
        $process->run($callback); // TODO : attention il peut il y avoir des exceptions qui sont levées par cette méthode, il faudrait faire un try/catch et les convertir en WebServerException !!!!

        // TODO : attention si on lance deux fois le serveur roadrunner sur la même url on obtien à la fois un message d'erreur dans la console et ensuite on affiche une seconde fois le même message. Donc c'est pas terrible !!!! Eventuellement afficher uniquement le message "Could not start Server." et puis c'est tout !!!
        // https://github.com/symfony/web-server-bundle/blob/c283d46b40b1c9dee20771433a19fa7f4a9bb97a/WebServer.php#L57
        if (! $process->isSuccessful()) {
            // TODO : afficher seulement la ligne de commande ($process->getCommandLine()) et le getErrorOutput dans le message de l'exception ??? Attention le getErrorOutput peut être vide !!!
            $this->error(
                sprintf('Could not start Server. Exit code: %d (%s). Error output: "%s".',
                    $process->getExitCode(),
                    $process->getExitCodeText(),
                    $process->getErrorOutput()
                )
            );
        }
    }

    /**
     * Since PHP 8, @ Error Suppression operator does not silent fatal errors anymore.
     * So the fsockopen is decorated with an error_reporting() function.
     *
     * @see https://php.watch/versions/8.0/fatal-error-suppression for more.
     *
     * @throws WebServerException
     */
    protected function assertAdressAvailable(string $hostname, int $port): void
    {
        $currentState = error_reporting();
        error_reporting(0);
        $resource = fsockopen($hostname, $port);
        error_reporting($currentState);

        if (is_resource($resource)) {
            fclose($resource);
            $this->error(sprintf('The port %d is already in use.', $port));
        }
    }

    // TODO : améliorer le code; exemple avec des événements :
    //https://github.com/huang-yi/swoole-watcher/blob/master/src/Watcher.php#L76
    //https://github.com/seregazhuk/reactphp-fswatch/blob/master/src/FsWatch.php#L40
    private function getOutputCallback(): callable
    {
        $output = $this->output;

        // TODO : virer le static et utiliser $this->output pour éviter la ligne de code "$output = $this->output; et xxx use($output)" !!!!
        // TODO : pour gerer le mode quiet il serait plus simple de faire un $output->isQuiet et si c'est cas on fait un early exit via un "return".
        return static function (string $type, string $buffer) use ($output) {
            if (Process::ERR === $type && $output instanceof ConsoleOutputInterface) { // TODO : faire en sorte d'éviter cette dépendance vers la classe Process dans la partie "use" de cette classe
                $output = $output->getErrorOutput();
            }
            $output->write($buffer, false, OutputInterface::OUTPUT_RAW);
        };
    }




    /**
     * Returns the list of signals to subscribe.
     *
     * @return array
     */
    public function getSubscribedSignals(): array
    {
        return [SIGINT, SIGTERM];
    }

    /**
     * The method will be called when the application is signaled.
     *
     * @param  int  $signal
     * @return void
     */
    public function handleSignal(int $signal): void
    {
        //$this->stopServer();
        $this->info('Stopping server...');
    }
}
