<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use TYPO3\CMS\Backend\Backend\Event\SystemInformationToolbarCollectorEvent;
use TYPO3\CMS\Backend\Template\Components\ModifyButtonBarEvent;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\DependencyInjection\SingletonPass;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Dashboard\WidgetRegistry;
use WebVision\Deepltranslate\Core\Client;
use WebVision\Deepltranslate\Core\ClientInterface;
use WebVision\Deepltranslate\Core\Command\GlossaryCleanupCommand;
use WebVision\Deepltranslate\Core\Command\GlossaryListCommand;
use WebVision\Deepltranslate\Core\Command\GlossarySyncCommand;
use WebVision\Deepltranslate\Core\Controller\Backend\AjaxController;
use WebVision\Deepltranslate\Core\Controller\GlossarySyncController;
use WebVision\Deepltranslate\Core\Event\Listener\GlossarySyncButtonProvider;
use WebVision\Deepltranslate\Core\Event\Listener\UsageToolBarEventListener;
use WebVision\Deepltranslate\Core\Form\Item\SiteConfigSupportedLanguageItemsProcFunc;
use WebVision\Deepltranslate\Core\Form\User\HasFormalitySupport;
use WebVision\Deepltranslate\Core\Hooks\Glossary\UpdatedGlossaryEntryTermHook;
use WebVision\Deepltranslate\Core\Hooks\TranslateHook;
use WebVision\Deepltranslate\Core\Hooks\UsageProcessAfterFinishHook;
use WebVision\Deepltranslate\Core\Service\DeeplGlossaryService;
use WebVision\Deepltranslate\Core\Service\DeeplService;
use WebVision\Deepltranslate\Core\Service\IconOverlayGenerator;
use WebVision\Deepltranslate\Core\Service\LanguageService;
use WebVision\Deepltranslate\Core\Service\ProcessingInstruction;
use WebVision\Deepltranslate\Core\Service\UsageService;
use WebVision\Deepltranslate\Core\Widgets\UsageWidget;

return function (ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder) {
    $typo3version = new Typo3Version();

    $services = $containerConfigurator
        ->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Main DI
    $services
        ->load('WebVision\\Deepltranslate\\Core\\', '../Classes/')
        ->exclude('../Classes/{Domain/Model,Override/Core12}');

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
        ->set(ProcessingInstruction::class)
        ->arg('$runtimeCache', service('cache.runtime'));
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
        ->registerForAutoconfiguration(HasFormalitySupport::class)
        ->addTag('deepl.HasFormalitySupport');

    $containerBuilder
        ->addCompilerPass(new SingletonPass('deepl.UpdatedGlossaryEntryTermHook'));
    $containerBuilder
        ->addCompilerPass(new SingletonPass('deepl.TranslateHook'));
    $containerBuilder
        ->addCompilerPass(new SingletonPass('deepl.SiteConfigSupportedLanguageItemsProcFunc'));
    $containerBuilder
        ->addCompilerPass(new SingletonPass('deepl.HasFormalitySupport'));

    // register Events
    $services
        ->set(GlossarySyncButtonProvider::class)
        ->tag(
            'event.listener',
            [
                'identifier' => 'glossary.syncbutton',
                'event' => ModifyButtonBarEvent::class,
            ]
        );

    $services
        ->set(UsageToolBarEventListener::class)
        ->tag(
            'event.listener',
            [
                'identifier' => 'deepl.usages',
                'event' => SystemInformationToolbarCollectorEvent::class,
            ]
        );

    /**
     * Check if WidgetRegistry is defined, which means that EXT:dashboard is available.
     * Registration directly in Services.yaml will break without EXT:dashboard installed!
     */
    if ($containerBuilder->hasDefinition(WidgetRegistry::class)) {
        $services->set('widgets.deepltranslate.widget.useswidget')
            ->class(UsageWidget::class)
            ->arg('$view', new Reference('dashboard.views.widget'))
            ->arg('$options', [])
            ->tag('dashboard.widget', [
                'identifier' => 'widgets-deepl-uses',
                'groupNames' => 'deepl',
                'title' => 'LLL:EXT:deepltranslate_core/Resources/Private/Language/locallang.xlf:widgets.deepltranslate.widget.useswidget.title',
                'description' => 'LLL:EXT:deepltranslate_core/Resources/Private/Language/locallang.xlf:widgets.deepltranslate.widget.useswidget.description',
                'iconIdentifier' => 'content-widget-list',
                'height' => 'small',
                'width' => 'small',
            ])
        ;
    }
};
