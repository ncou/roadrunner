<?php

declare(strict_types=1);

namespace Chiron\RoadRunner;

use Chiron\Dispatcher\AbstractDispatcher;
use Chiron\ErrorHandler\ErrorHandler;
use Chiron\Http\Http;
use Spiral\RoadRunner\PSR7Client;
use Throwable;

//https://github.com/spiral/framework/blob/master/src/Http/RrDispatcher.php

final class RrDispatcher extends AbstractDispatcher
{
    public function canDispatch(): bool
    {
        return PHP_SAPI === 'cli' && env('RR') !== null;
    }

    /**
     * @param \Chiron\Http\Http                 $http
     * @param \Spiral\RoadRunner\PSR7Client     $client
     * @param \Chiron\ErrorHandler\ErrorHandler $errorHandler
     */
    protected function perform(Http $http, PSR7Client $client, ErrorHandler $errorHandler): void
    {
        // TODO : code à améliorer pour savoir si on est en debug ou non et donc si les exceptions doivent afficher le détail (stacktrace notamment) !!!!
        $verbose = true;

        while ($request = $client->acceptRequest()) {
            // TODO : c'est quoi l'utilité de ce code (le try/catch Throwable) versus le code qui est déjà présent dans le ErrorHandlerMiddleware ????
            try {
                $response = $http->run($request);
            } catch (Throwable $e) {
                // TODO : il faudrait plutot utiliser le RegisterErrorHandler::renderException($e) pour générer le body de la réponse !!!!
                $response = $errorHandler->renderException($e, $request, $verbose);
            }

            $client->respond($response);
            //gc_collect_cycles();
        }
    }
}
