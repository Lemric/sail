<?php

// This file has been auto-generated by the Symfony Dependency Injection Component
// You can reference it in the "opcache.preload" php.ini setting on PHP >= 7.4 when preloading is desired

use Symfony\Component\DependencyInjection\Dumper\Preloader;

if (in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
    return;
}

require dirname(__DIR__, 5).'/vendor/autoload.php';
(require __DIR__.'/Lemric_Sail_Tests_Fixtures_TestKernelTestDebugContainer.php')->set(\ContainerRmieRr7\Lemric_Sail_Tests_Fixtures_TestKernelTestDebugContainer::class, null);
require __DIR__.'/ContainerRmieRr7/RequestPayloadValueResolverGhostF14d8e1.php';
require __DIR__.'/ContainerRmieRr7/getTest_ServiceContainerService.php';
require __DIR__.'/ContainerRmieRr7/getTest_PrivateServicesLocatorService.php';
require __DIR__.'/ContainerRmieRr7/getTest_Client_HistoryService.php';
require __DIR__.'/ContainerRmieRr7/getTest_Client_CookiejarService.php';
require __DIR__.'/ContainerRmieRr7/getTest_ClientService.php';
require __DIR__.'/ContainerRmieRr7/getServicesResetterService.php';
require __DIR__.'/ContainerRmieRr7/getSecrets_VaultService.php';
require __DIR__.'/ContainerRmieRr7/getSecrets_EnvVarLoaderService.php';
require __DIR__.'/ContainerRmieRr7/getSecrets_DecryptionKeyService.php';
require __DIR__.'/ContainerRmieRr7/getErrorHandler_ErrorRenderer_HtmlService.php';
require __DIR__.'/ContainerRmieRr7/getErrorControllerService.php';
require __DIR__.'/ContainerRmieRr7/getDebug_FileLinkFormatterService.php';
require __DIR__.'/ContainerRmieRr7/getDebug_ErrorHandlerConfiguratorService.php';
require __DIR__.'/ContainerRmieRr7/getContainer_GetenvService.php';
require __DIR__.'/ContainerRmieRr7/getContainer_GetRoutingConditionServiceService.php';
require __DIR__.'/ContainerRmieRr7/getContainer_EnvVarProcessorsLocatorService.php';
require __DIR__.'/ContainerRmieRr7/getContainer_EnvVarProcessorService.php';
require __DIR__.'/ContainerRmieRr7/getCache_SystemClearerService.php';
require __DIR__.'/ContainerRmieRr7/getCache_SystemService.php';
require __DIR__.'/ContainerRmieRr7/getCache_GlobalClearerService.php';
require __DIR__.'/ContainerRmieRr7/getCache_DefaultMarshallerService.php';
require __DIR__.'/ContainerRmieRr7/getCache_AppClearerService.php';
require __DIR__.'/ContainerRmieRr7/getCache_AppService.php';
require __DIR__.'/ContainerRmieRr7/getArgumentResolver_VariadicService.php';
require __DIR__.'/ContainerRmieRr7/getArgumentResolver_SessionService.php';
require __DIR__.'/ContainerRmieRr7/getArgumentResolver_ServiceService.php';
require __DIR__.'/ContainerRmieRr7/getArgumentResolver_RequestAttributeService.php';
require __DIR__.'/ContainerRmieRr7/getArgumentResolver_RequestService.php';
require __DIR__.'/ContainerRmieRr7/getArgumentResolver_QueryParameterValueResolverService.php';
require __DIR__.'/ContainerRmieRr7/getArgumentResolver_DefaultService.php';
require __DIR__.'/ContainerRmieRr7/getArgumentResolver_DatetimeService.php';
require __DIR__.'/ContainerRmieRr7/getArgumentResolver_BackedEnumResolverService.php';
require __DIR__.'/ContainerRmieRr7/get_ServiceLocator_GIuJv7eService.php';

$classes = [];
$classes[] = 'Symfony\Bundle\FrameworkBundle\FrameworkBundle';
$classes[] = 'Symfony\Component\DependencyInjection\ServiceLocator';
$classes[] = 'Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory';
$classes[] = 'Symfony\Component\HttpKernel\Controller\ArgumentResolver';
$classes[] = 'Symfony\Component\HttpKernel\Controller\ArgumentResolver\BackedEnumValueResolver';
$classes[] = 'Symfony\Component\HttpKernel\Controller\ArgumentResolver\DateTimeValueResolver';
$classes[] = 'Symfony\Component\HttpKernel\Controller\ArgumentResolver\DefaultValueResolver';
$classes[] = 'Symfony\Component\HttpKernel\Controller\ArgumentResolver\QueryParameterValueResolver';
$classes[] = 'Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestValueResolver';
$classes[] = 'Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestAttributeValueResolver';
$classes[] = 'Symfony\Component\HttpKernel\Controller\ArgumentResolver\ServiceValueResolver';
$classes[] = 'Symfony\Component\HttpKernel\Controller\ArgumentResolver\SessionValueResolver';
$classes[] = 'Symfony\Component\HttpKernel\Controller\ArgumentResolver\VariadicValueResolver';
$classes[] = 'Symfony\Component\Cache\Adapter\FilesystemAdapter';
$classes[] = 'Symfony\Component\HttpKernel\CacheClearer\Psr6CacheClearer';
$classes[] = 'Symfony\Component\Cache\Marshaller\DefaultMarshaller';
$classes[] = 'Symfony\Component\Cache\Adapter\AdapterInterface';
$classes[] = 'Symfony\Component\Cache\Adapter\AbstractAdapter';
$classes[] = 'Symfony\Component\DependencyInjection\EnvVarProcessor';
$classes[] = 'Symfony\Component\HttpKernel\EventListener\CacheAttributeListener';
$classes[] = 'Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver';
$classes[] = 'Symfony\Component\HttpKernel\EventListener\DebugHandlersListener';
$classes[] = 'Symfony\Component\HttpKernel\Debug\ErrorHandlerConfigurator';
$classes[] = 'Symfony\Component\ErrorHandler\ErrorRenderer\FileLinkFormatter';
$classes[] = 'Symfony\Component\HttpKernel\EventListener\DisallowRobotsIndexingListener';
$classes[] = 'Symfony\Component\HttpKernel\Controller\ErrorController';
$classes[] = 'Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer';
$classes[] = 'Symfony\Component\EventDispatcher\EventDispatcher';
$classes[] = 'Symfony\Component\HttpKernel\EventListener\ErrorListener';
$classes[] = 'Symfony\Component\Runtime\Runner\Symfony\HttpKernelRunner';
$classes[] = 'Symfony\Component\Runtime\Runner\Symfony\ResponseRunner';
$classes[] = 'Symfony\Component\Runtime\SymfonyRuntime';
$classes[] = 'Symfony\Component\HttpKernel\HttpKernel';
$classes[] = 'Symfony\Component\HttpKernel\EventListener\LocaleListener';
$classes[] = 'Symfony\Component\HttpKernel\Log\Logger';
$classes[] = 'Symfony\Component\HttpFoundation\RequestStack';
$classes[] = 'Symfony\Component\HttpKernel\EventListener\ResponseListener';
$classes[] = 'Symfony\Component\String\LazyString';
$classes[] = 'Symfony\Component\DependencyInjection\StaticEnvVarLoader';
$classes[] = 'Symfony\Bundle\FrameworkBundle\Secrets\SodiumVault';
$classes[] = 'Symfony\Component\DependencyInjection\ContainerInterface';
$classes[] = 'Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter';
$classes[] = 'Symfony\Bundle\FrameworkBundle\KernelBrowser';
$classes[] = 'Symfony\Component\BrowserKit\CookieJar';
$classes[] = 'Symfony\Component\BrowserKit\History';
$classes[] = 'Symfony\Bundle\FrameworkBundle\Test\TestContainer';
$classes[] = 'Symfony\Component\HttpKernel\EventListener\ValidateRequestListener';

$preloaded = Preloader::preload($classes);
