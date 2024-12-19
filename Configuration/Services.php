<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Core\DependencyInjection\SingletonPass;
use TYPO3\CMS\Dashboard\WidgetRegistry;
use WebVision\Deepltranslate\Core\Form\Item\SiteConfigSupportedLanguageItemsProcFunc;
use WebVision\Deepltranslate\Core\Form\User\HasFormalitySupport;
use WebVision\Deepltranslate\Core\Hooks\TranslateHook;
use WebVision\Deepltranslate\Core\Service\UsageService;
use WebVision\Deepltranslate\Core\Widgets\UsageWidget;

return function (ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder) {
    $services = $containerConfigurator
        ->services();

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
        ->addCompilerPass(new SingletonPass('deepl.TranslateHook'));
    $containerBuilder
        ->addCompilerPass(new SingletonPass('deepl.SiteConfigSupportedLanguageItemsProcFunc'));
    $containerBuilder
        ->addCompilerPass(new SingletonPass('deepl.HasFormalitySupport'));

    /**
     * Check if WidgetRegistry is defined, which means that EXT:dashboard is available.
     * Registration directly in Services.yaml will break without EXT:dashboard installed!
     */
    if ($containerBuilder->hasDefinition(WidgetRegistry::class)) {
        $services->set('widgets.deepltranslate.widget.useswidget')
            ->class(UsageWidget::class)
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class))
            ->arg('$usageService', new Reference(UsageService::class))
            ->arg('$options', [])
            ->tag('dashboard.widget', [
                'identifier' => 'widgets-deepl-uses',
                'groupNames' => 'deepl',
                'title' => 'LLL:EXT:deepltranslate_core/Resources/Private/Language/locallang.xlf:widgets.deepltranslate.widget.useswidget.title',
                'description' => 'LLL:EXT:deepltranslate_core/Resources/Private/Language/locallang.xlf:widgets.deepltranslate.widget.useswidget.description',
                'iconIdentifier' => 'content-widget-list',
                'height' => 'small',
                'width' => 'small',
            ]);
    }
};
