<?php

namespace Container9FxPh29;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getCacheWarmerService extends Lemric_Sail_Tests_Fixtures_TestKernelTestDebugContainer
{
    /**
     * Gets the public 'cache_warmer' shared service.
     *
     * @return \Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerAggregate
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 6).'/vendor/symfony/http-kernel/CacheWarmer/WarmableInterface.php';
        include_once \dirname(__DIR__, 6).'/vendor/symfony/http-kernel/CacheWarmer/CacheWarmerInterface.php';
        include_once \dirname(__DIR__, 6).'/vendor/symfony/http-kernel/CacheWarmer/CacheWarmerAggregate.php';

        return $container->services['cache_warmer'] = new \Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerAggregate(new RewindableGenerator(function () use ($container) {
            yield 0 => ($container->privates['config_builder.warmer'] ?? $container->load('getConfigBuilder_WarmerService'));
        }, 1), true, ($container->targetDir.''.'/Lemric_Sail_Tests_Fixtures_TestKernelTestDebugContainerDeprecations.log'));
    }
}
