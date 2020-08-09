<?php

//https://github.com/php-services/http-factory-nyholm/blob/master/src/NyholmHttpFactoryServiceProvider.php

//https://github.com/userfrosting/UserFrosting/blob/master/app/system/ServicesProvider.php
//https://github.com/slimphp/Slim/blob/3.x/Slim/DefaultServicesProvider.php
declare(strict_types=1);

namespace Chiron\RoadRunner\Provider;

//use Chiron\Http\Middleware\ErrorHandlerMiddleware;
use Chiron\Bootload\ServiceProvider\ServiceProviderInterface;
use Chiron\Container\BindingInterface;
use Chiron\Container\Container;
use Closure;
use Spiral\Goridge\StreamRelay;
use Spiral\RoadRunner\PSR7Client;
use Spiral\RoadRunner\Worker;

// TODO : code à améliorer : https://github.com/spiral/framework/blob/98654e9d217f7d4ca994f27c68cfde0b70ac67d5/src/Bootloader/ServerBootloader.php#L31

/**
 * Chiron RoadRunner services provider.
 */
class RrServiceProvider implements ServiceProviderInterface
{
    /**
     * Register Chiron system services.
     *
     * @param Container $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(BindingInterface $container): void
    {
        // *** register factories ***
        $container->bind(PSR7Client::class, Closure::fromCallable([$this, 'psr7Client']));
    }

    private function psr7Client(): PSR7Client
    {
        $relay = new StreamRelay(STDIN, STDOUT);

        return new PSR7Client(new Worker($relay));
    }
}
