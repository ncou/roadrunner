<?php

declare(strict_types=1);

namespace Chiron\RoadRunner;

use Chiron\Core\Engine\AbstractEngine;
use Chiron\Http\Http;

//https://github.com/spiral/framework/blob/master/src/Http/RrDispatcher.php
//https://github.com/spiral/framework/blob/d17c175e85165456fbd2d841c8e81165e371675c/src/Framework/Http/RrDispatcher.php#L28

//https://github.com/spiral/framework/blob/d17c175e85165456fbd2d841c8e81165e371675c/src/Framework/Bootloader/Server/RoadRunnerBootloader.php#L60

// TODO : renommer la classe en RoadrunnerEngine
final class RrEngine extends AbstractEngine
{
    public function isActive(): bool
    {
        return PHP_SAPI === 'cli' && env('RR') !== null; // env('RR_MODE') === 'http' // eventuellement utiliser la constante pour 'http' ===> Spiral\RoadRunner\Environment\Mode::MODE_HTTP

        /** @link https://roadrunner.dev/docs/php-environment */
        //https://github.com/spiral/roadrunner-laravel/blob/master/src/Dumper/Dumper.php#L102
    }

    protected function perform(RrListener $roadrunner, Http $http): void
    {
        /*
        $roadrunner->onMessage = function (ServerRequestInterface $request) use ($http, $errorHandler) {
            // TODO : code à améliorer pour savoir si on est en debug ou non et donc si les exceptions doivent afficher le détail (stacktrace notamment) !!!!
            // https://github.com/yiisoft/yii-web/blob/ae3d1986fefd41e1f86f345b4ea57ca33326d4f2/src/ErrorHandler/ErrorCatcher.php#L132
            // https://github.com/yiisoft/yii-web/blob/54000c5e34d834efe61dce3ecd6ede36b86c31bd/src/ErrorHandler/ThrowableRendererInterface.php#L28

            // Return a psr7 ResponseInterface.
            return $http->handle($request);
        };*/

        // Callable used when a new request event is received.
        $roadrunner->onMessage = [$http, 'handle'];
        // Listen (loop wainting a request) and Emit the response.
        $roadrunner->listen();
    }
}
