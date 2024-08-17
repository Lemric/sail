<?php

namespace Container9FxPh29;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getTest_ServiceContainerService extends Lemric_Sail_Tests_Fixtures_TestKernelTestDebugContainer
{
    /**
     * Gets the public 'test.service_container' shared service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Test\TestContainer
     */
    public static function do($container, $lazyLoad = true)
    {
        return $container->services['test.service_container'] = new \Symfony\Bundle\FrameworkBundle\Test\TestContainer(($container->services['kernel'] ?? $container->get('kernel', 1)), 'test.private_services_locator', ['cache.default_clearer' => 'cache.app_clearer', 'Symfony\\Component\\EventDispatcher\\EventDispatcherInterface' => 'event_dispatcher', 'Symfony\\Contracts\\EventDispatcher\\EventDispatcherInterface' => 'event_dispatcher', 'Psr\\EventDispatcher\\EventDispatcherInterface' => 'event_dispatcher', 'Symfony\\Component\\HttpKernel\\HttpKernelInterface' => 'http_kernel', 'Symfony\\Component\\HttpFoundation\\RequestStack' => 'request_stack', 'Symfony\\Component\\HttpKernel\\KernelInterface' => 'kernel', 'Symfony\\Component\\Filesystem\\Filesystem' => 'filesystem', 'error_renderer.html' => 'error_handler.error_renderer.html', 'error_renderer' => 'error_handler.error_renderer.html', 'Psr\\Cache\\CacheItemPoolInterface' => 'cache.app', 'Symfony\\Contracts\\Cache\\CacheInterface' => 'cache.app', 'Symfony\\Contracts\\Cache\\TagAwareCacheInterface' => 'cache.app.taggable', 'Symfony\\Component\\ErrorHandler\\ErrorRenderer\\FileLinkFormatter' => 'debug.file_link_formatter', 'argument_resolver.controller_locator' => '.service_locator.GIuJv7e', 'Psr\\Log\\LoggerInterface' => 'logger']);
    }
}
