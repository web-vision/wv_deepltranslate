<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Hooks;

use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\Buttons\LinkButton;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use WebVision\WvDeepltranslate\Hooks\ButtonBarHook;

class ButtonBarHookTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'web-vision/wv_deepltranslate',
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

    /**
     * @test
     */
    public function showGlossarySynchronizationButton(): void
    {
        // User with Access
        $beUser = $this->setUpBackendUser(2);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($beUser);

        $buttons = $this->getButtonsAddsGlossarySyncButton(3);

        static::assertIsArray($buttons[ButtonBar::BUTTON_POSITION_LEFT][5] ?? null);
        // Assertions to check if the button has been added
        static::assertCount(1, $buttons[ButtonBar::BUTTON_POSITION_LEFT][5]);

        /** @var LinkButton $button */
        $button = $buttons[ButtonBar::BUTTON_POSITION_LEFT][5][0];
        static::assertInstanceOf(LinkButton::class, $button);
    }

    /**
     * @test
     */
    public function displayGlossarySynchronizationButtonBecauseNotRightModel(): void
    {
        // Backend-Admin User
        $beUser = $this->setUpBackendUser(2);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($beUser);
        // Create page with not correct module value

        $buttons = $this->getButtonsAddsGlossarySyncButton(4);

        static::assertIsArray($buttons);
        static::assertFalse(isset($buttons[ButtonBar::BUTTON_POSITION_LEFT][5]));
    }

    /**
     * @test
     */
    public function displayGlossarySynchronizationButtonBecauseNotAllowed(): void
    {
        // Backend-Admin User
        $beUser = $this->setUpBackendUser(3);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($beUser);
        // Create page with correct module value
        // But had no Access
        $buttons = $this->getButtonsAddsGlossarySyncButton(3);

        static::assertIsArray($buttons[ButtonBar::BUTTON_POSITION_LEFT][5] ?? null);
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
