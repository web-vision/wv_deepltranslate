<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use TYPO3\CMS\Core\DependencyInjection\SingletonPass;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Dashboard\WidgetRegistry;
use WebVision\Deepltranslate\Core\Core12\Widgets\UsageWidget as Core12UsageWidget;
use WebVision\Deepltranslate\Core\Form\Item\SiteConfigSupportedLanguageItemsProcFunc;
use WebVision\Deepltranslate\Core\Form\User\HasFormalitySupport;
use WebVision\Deepltranslate\Core\Hooks\TranslateHook;

return function (ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder) {
    $typo3version = new Typo3Version();
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
        if ($typo3version->getMajorVersion() >= 13) {
            // @todo Register TYPO3 v13 compatible UsageWidget implementation. (StandaloneView => ViewFactory)
        }
        /**
         * @todo Remove core12 usage widget when TYPO3 v12 support is removed along with {@see Core11UsageWidget}.
         */
        if ($typo3version->getMajorVersion() === 12) {
            $services->set('widgets.deepltranslate.widget.useswidget')
                ->class(Core12UsageWidget::class)
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
                ]);
        }
    }
};
