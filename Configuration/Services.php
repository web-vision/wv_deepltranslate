<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use TYPO3\CMS\Backend\Backend\Event\SystemInformationToolbarCollectorEvent;
use TYPO3\CMS\Backend\Template\Components\ModifyButtonBarEvent;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\DependencyInjection\SingletonPass;
use TYPO3\CMS\Core\Information\Typo3Version;
use WebVision\WvDeepltranslate\Client;
use WebVision\WvDeepltranslate\ClientInterface;
use WebVision\WvDeepltranslate\Command\GlossaryCleanupCommand;
use WebVision\WvDeepltranslate\Command\GlossaryListCommand;
use WebVision\WvDeepltranslate\Command\GlossarySyncCommand;
use WebVision\WvDeepltranslate\Controller\Backend\AjaxController;
use WebVision\WvDeepltranslate\Controller\GlossarySyncController;
use WebVision\WvDeepltranslate\Event\Listener\GlossarySyncButtonProvider;
use WebVision\WvDeepltranslate\Event\Listener\UsageToolBarEventListener;
use WebVision\WvDeepltranslate\Form\Item\SiteConfigSupportedLanguageItemsProcFunc;
use WebVision\WvDeepltranslate\Hooks\Glossary\UpdatedGlossaryEntryTermHook;
use WebVision\WvDeepltranslate\Hooks\TranslateHook;
use WebVision\WvDeepltranslate\Hooks\UsageProcessAfterFinishHook;
use WebVision\WvDeepltranslate\Service\DeeplGlossaryService;
use WebVision\WvDeepltranslate\Service\DeeplService;
use WebVision\WvDeepltranslate\Service\IconOverlayGenerator;
use WebVision\WvDeepltranslate\Service\LanguageService;
use WebVision\WvDeepltranslate\Service\UsageService;

return function (ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder) {
    $typo3version = new Typo3Version();

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
        ->tag(
            'console.command',
            [
                'command' => 'deepl:glossary:cleanup',
                'description' => 'Cleanup Glossary entries in DeepL Database',
                'schedulable' => true,
            ]
        );
    $services
        ->set(GlossarySyncCommand::class)
        ->tag(
            'console.command',
            [
                'command' => 'deepl:glossary:sync',
                'description' => 'Sync all glossaries to DeepL API',
                'schedulable' => true,
            ]
        );
    $services
        ->set(GlossaryListCommand::class)
        ->tag(
            'console.command',
            [
                'command' => 'deepl:glossary:list',
                'description' => 'List Glossary entries or entries by glossary_id',
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
        ->set(IconOverlayGenerator::class)
        ->public();
    $services
        ->set(UsageService::class)
        ->public();
    $services
        ->set(UsageProcessAfterFinishHook::class)
        ->public();

    $services
        ->set(AjaxController::class)
        ->public();
    $services
        ->set(GlossarySyncController::class)
        ->public();

    $services->alias(ClientInterface::class, Client::class);

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
                    'event' => ModifyButtonBarEvent::class,
                ]
            );
    }

    $services
        ->set(UsageToolBarEventListener::class)
        ->tag(
            'event.listener',
            [
                'identifier' => 'deepl.usages',
                'event' => SystemInformationToolbarCollectorEvent::class,
            ]
        );
};
