<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use WebVision\Deepltranslate\Core\ClientInterface;

return function (ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder) {
    $services = $containerConfigurator->services();
    $services
        ->defaults()
        ->autowire()
        ->autoconfigure();

    // Set the ClientInterface to public for testing purpose only. This is needed, that
    // functional testcases can set a special configured or mocked service
    // instance for the alias. No need to have it public in general.
    $services
        ->set(ClientInterface::class)
        ->public();
};
