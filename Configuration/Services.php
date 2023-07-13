<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use TYPO3\CMS\Backend\Template\Components\ModifyButtonBarEvent;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\DependencyInjection\SingletonPass;
use WebVision\WvDeepltranslate\Command\GlossaryCleanupCommand;
use WebVision\WvDeepltranslate\Command\GlossaryListCommand;
use WebVision\WvDeepltranslate\Command\GlossarySyncCommand;
use WebVision\WvDeepltranslate\Controller\Backend\AjaxController;
use WebVision\WvDeepltranslate\Controller\GlossarySyncController;
use WebVision\WvDeepltranslate\Event\Listener\GlossarySyncButtonProvider;
use WebVision\WvDeepltranslate\Form\Item\SiteConfigSupportedLanguageItemsProcFunc;
use WebVision\WvDeepltranslate\Hooks\Glossary\UpdatedGlossaryEntryTermHook;
use WebVision\WvDeepltranslate\Hooks\TranslateHook;
use WebVision\WvDeepltranslate\Service\DeeplGlossaryService;
use WebVision\WvDeepltranslate\Service\DeeplService;
use WebVision\WvDeepltranslate\Service\LanguageService;

return function (ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder) {
    $typo3version = new \TYPO3\CMS\Core\Information\Typo3Version();

    $services = $containerConfigurator
        ->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Main DI
    $services
        ->load('WebVision\\WvDeepltranslate\\', '../Classes/')
        ->exclude('../Classes/{Domain/Model,Override/DatabaseRecordList.php,Override/Core11,Override/Core12}');

    // register console commands
    $services
        ->set(GlossaryCleanupCommand::class)
        ->arg('$name', 'deepl:glossary:cleanup')
        ->tag(
            'console.command',
            [
                'command' => 'deepl:glossary:cleanup',
                'schedulable' => true,
            ]
        );
    $services
        ->set(GlossarySyncCommand::class)
        ->arg('$name', 'deepl:glossary:sync')
        ->tag(
            'console.command',
            [
                'command' => 'deepl:glossary:sync',
                'schedulable' => true,
            ]
        );
    $services
        ->set(GlossaryListCommand::class)
        ->arg('$name', 'deepl:glossary:list')
        ->tag(
            'console.command',
            [
                'command' => 'deepl:glossary:list',
                'schedulable' => false,
            ]
        );

    // add caching
    $services->set('cache.wvdeepltranslate')
        ->class(FrontendInterface::class)
        ->factory([service(CacheManager::class), 'getCache'])
        ->args(['wvdeepltranslate']);
    $services
        ->set(DeeplService::class)
        ->public()
        ->arg('$cache', service('cache.wvdeepltranslate'));
    $services
        ->set(DeeplGlossaryService::class)
        ->public()
        ->arg('$cache', service('cache.wvdeepltranslate'));
    $services
        ->set(LanguageService::class)
        ->public();

    $services
        ->set(AjaxController::class)
        ->public();

    $services
        ->set(GlossarySyncController::class)
        ->public();

    $containerBuilder
        ->registerForAutoconfiguration(UpdatedGlossaryEntryTermHook::class)
        ->addTag('deepl.UpdatedGlossaryEntryTermHook');
    $containerBuilder
        ->registerForAutoconfiguration(TranslateHook::class)
        ->addTag('deepl.TranslateHook');
    $containerBuilder
        ->registerForAutoconfiguration(SiteConfigSupportedLanguageItemsProcFunc::class)
        ->addTag('deepl.SiteConfigSupportedLanguageItemsProcFunc');

    $containerBuilder
        ->addCompilerPass(new SingletonPass('deepl.UpdatedGlossaryEntryTermHook'));
    $containerBuilder
        ->addCompilerPass(new SingletonPass('deepl.TranslateHook'));
    $containerBuilder
        ->addCompilerPass(new SingletonPass('deepl.SiteConfigSupportedLanguageItemsProcFunc'));

    // register Events
    if ($typo3version->getMajorVersion() >= 12) {
        $services
            ->set(GlossarySyncButtonProvider::class)
            ->tag(
                'event.listener',
                [
                    'identifier' => 'glossary.syncbutton',
                    'event' => ModifyButtonBarEvent::class
                ]
            );
    }
};
