<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Tests\Functional\Hooks;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\Buttons\LinkButton;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use WebVision\Deepltranslate\Core\Hooks\ButtonBarHook;

class ButtonBarHookTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'typo3/cms-setup',
        'typo3/cms-scheduler',
    ];

    protected array $testExtensionsToLoad = [
        'web-vision/deepltranslate-core',
    ];

    public function setUp(): void
    {
        $this->configurationToUseInTestInstance = array_merge(
            $this->configurationToUseInTestInstance,
            require __DIR__ . '/../Fixtures/ExtensionConfig.php'
        );

        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/ButtonBarHookFixtures.csv');

    }

    #[Test]
    public function showGlossarySynchronizationButton(): void
    {
        // User with Access
        $backendUser = $this->setUpBackendUser(2);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($backendUser);

        $buttons = $this->getButtonsAddsGlossarySyncButton(3);

        static::assertIsArray($buttons[ButtonBar::BUTTON_POSITION_LEFT]);
        static::assertIsArray($buttons[ButtonBar::BUTTON_POSITION_LEFT][5]);
        // Assertions to check if the button has been added
        static::assertCount(1, $buttons[ButtonBar::BUTTON_POSITION_LEFT][5]);

        /** @var LinkButton $button */
        $button = $buttons[ButtonBar::BUTTON_POSITION_LEFT][5][0];
        static::assertInstanceOf(LinkButton::class, $button);
    }

    #[Test]
    public function displayGlossarySynchronizationButtonBecauseNotRightModel(): void
    {
        // Backend-Admin User
        $backendUser = $this->setUpBackendUser(2);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($backendUser);

        $buttons = $this->getButtonsAddsGlossarySyncButton(4);

        static::assertIsArray($buttons);
        static::assertFalse(isset($buttons[ButtonBar::BUTTON_POSITION_LEFT]));
        static::assertFalse(isset($buttons[ButtonBar::BUTTON_POSITION_LEFT][5]));
    }

    #[Test]
    public function displayGlossarySynchronizationButtonBecauseNotAllowed(): void
    {
        // Backend-Admin User
        $backendUser = $this->setUpBackendUser(3);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($backendUser);

        $buttons = $this->getButtonsAddsGlossarySyncButton(3);

        static::assertIsArray($buttons[ButtonBar::BUTTON_POSITION_LEFT][5]);
    }

    /**
     * @return mixed[]
     */
    private function getButtonsAddsGlossarySyncButton(int $pageId)
    {
        $request = (new ServerRequest('https://www.example.com/typo3', 'GET'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE)
        ;
        $GLOBALS['TYPO3_REQUEST'] = $request = $request
            ->withAttribute('normalizedParams', NormalizedParams::createFromRequest($request))
            ->withQueryParams(['id' => $pageId])
        ;

        $subject = new ButtonBarHook();

        /** @var ButtonBar $buttonBar */
        $buttonBar = GeneralUtility::makeInstance(ButtonBar::class);

        $params = [
            'id' => $pageId,
            'buttons' => [],
        ];
        return $subject->getButtons($params, $buttonBar);
    }
}
