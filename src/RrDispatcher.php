<?php

declare(strict_types=1);

namespace Chiron\RoadRunner;

use Chiron\Core\Dispatcher\AbstractDispatcher;
use Chiron\Http\ErrorHandler\HttpErrorHandler;
use Psr\Http\Message\ServerRequestInterface;
use Chiron\Http\Http;
use Spiral\RoadRunner\PSR7Client;
use Throwable;

//https://github.com/spiral/framework/blob/master/src/Http/RrDispatcher.php
//https://github.com/spiral/framework/blob/d17c175e85165456fbd2d841c8e81165e371675c/src/Framework/Http/RrDispatcher.php#L28

//https://github.com/spiral/framework/blob/d17c175e85165456fbd2d841c8e81165e371675c/src/Framework/Bootloader/Server/RoadRunnerBootloader.php#L60

// TODO : renommer la classe en RoadrunnerDispatcher
final class RrDispatcher extends AbstractDispatcher
{
    public function canDispatch(): bool
    {
        return PHP_SAPI === 'cli' && env('RR') !== null; // env('RR_MODE') === 'http' // eventuellement utiliser la constante pour 'http' ===> Spiral\RoadRunner\Environment\Mode::MODE_HTTP
    }

    protected function perform(RrListener $roadrunner, Http $http, HttpErrorHandler $errorHandler): void
    {
        $roadrunner->onMessage = function (ServerRequestInterface $request) use ($http, $errorHandler) {
            // TODO : code à améliorer pour savoir si on est en debug ou non et donc si les exceptions doivent afficher le détail (stacktrace notamment) !!!!
            // https://github.com/yiisoft/yii-web/blob/ae3d1986fefd41e1f86f345b4ea57ca33326d4f2/src/ErrorHandler/ErrorCatcher.php#L132
            // https://github.com/yiisoft/yii-web/blob/54000c5e34d834efe61dce3ecd6ede36b86c31bd/src/ErrorHandler/ThrowableRendererInterface.php#L28
            $verbose = true;

            // TODO : c'est quoi l'utilité de ce code (le try/catch Throwable) versus le code qui est déjà présent dans le ErrorHandlerMiddleware ????
            try {
                $response = $http->handle($request);
            } catch (Throwable $e) {
                // TODO : il faudrait plutot utiliser le RegisterErrorHandler::renderException($e) pour générer le body de la réponse !!!!
                $response = $errorHandler->renderException($e, $request, $verbose);
            }

            return $response;
        };

        $roadrunner->listen();
    }
}
