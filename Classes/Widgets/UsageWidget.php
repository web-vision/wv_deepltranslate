<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Widgets;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;
use WebVision\Deepltranslate\Core\Service\UsageService;

class UsageWidget implements WidgetInterface
{
    private WidgetConfigurationInterface $configuration;

    private StandaloneView $view;

    /**
     * @var array<string, mixed>
     */
    private array $options;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        WidgetConfigurationInterface $configuration,
        StandaloneView $view,
        array $options = []
    ) {
        $this->configuration = $configuration;
        $this->view = $view;
        $this->options = $options;
    }

    public function renderWidgetContent(): string
    {
        /** @var UsageService $usageService */
        $usageService = GeneralUtility::makeInstance(UsageService::class);

        // Workaround to make the widget template available in two TYPO3 versions
        $templateRootPaths = $this->view->getTemplateRootPaths();
        $templateRootPaths[1718368476557] = 'EXT:deepltranslate_core/Resources/Private/Backend/Templates/';
        $this->view->setTemplateRootPaths($templateRootPaths);

        $currentUsage = $usageService->getCurrentUsage();

        $this->view->assignMultiple([
            'usages' => [
                [
                    'label' => $this->getLanguageService()->sL('LLL:EXT:deepltranslate_core/Resources/Private/Language/locallang.xlf:widgets.deepltranslate.widget.useswidget.character'),
                    'usage' => $currentUsage !== null ? $currentUsage->character : [],
                ],
            ],
            'options' => $this->options,
            'configuration' => $this->configuration,
        ]);

        return $this->view->render('Widget/UsageWidget');
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
