<?php

declare(strict_types=1);

namespace Chiron\RoadRunner;

use Chiron\Core\Dispatcher\AbstractDispatcher;
use Chiron\Http\ErrorHandler\HttpErrorHandler;
use Chiron\Http\Http;
use Spiral\RoadRunner\PSR7Client;
use Throwable;

// TODO : renommer la classe en RoadRunnerListener
final class RrListener
{
    /** @var callable */
    public $onMessage;
    /** @var PSR7Client */
    private $client;

    public function __construct(PSR7Client $client)
    {
        $this->client = $client;
    }

    public function listen(): void
    {
        while ($request = $this->client->acceptRequest()) {
            $response = call_user_func($this->onMessage, $request);
            $this->client->respond($response);
            //gc_collect_cycles();
        }
    }
}
