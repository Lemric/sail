<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function(ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
    ;
    $services->load('Lemric\\Sail\\', '../src/')
        ->exclude('../src/{DependencyInjection}');
};