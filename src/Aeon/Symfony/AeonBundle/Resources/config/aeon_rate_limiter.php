<?php

declare(strict_types=1);

use Aeon\Symfony\AeonBundle\RateLimiter\RateLimiters;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();

    $services->set('rate_limiters', RateLimiters::class)
        ->alias(RateLimiters::class, 'rate_limiters')
        ->public();
};
