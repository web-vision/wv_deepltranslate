<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Regression;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Service\DeeplGlossaryService;
use WebVision\WvDeepltranslate\Tests\Functional\AbstractDeepLTestCase;
use WebVision\WvDeepltranslate\Tests\Functional\Fixtures\Traits\SiteBasedTestTrait;

final class GlossaryRegressionTest extends AbstractDeepLTestCase
{
    use SiteBasedTestTrait;

    protected const LANGUAGE_PRESETS = [
        'EN' => [
            'id' => 0,
            'title' => 'English',
            'locale' => 'en_US.UTF-8',
            'iso' => 'en',
            'hrefLang' => 'en-US',
            'direction' => '',
            'custom' => [
                'deeplTargetLanguage' => '',
            ],
        ],
        'DE' => [
            'id' => 1,
            'title' => 'Deutsch',
            'locale' => 'de_DE',
            'iso' => 'de',
            'hrefLang' => 'de-DE',
            'direction' => '',
            'custom' => [
                'deeplTargetLanguage' => 'DE',
            ],
        ],
    ];

    protected array $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'wv_deepltranslate' => [
                'apiKey' => 'mock_server',
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $site = $this->buildSiteConfiguration(1, '/', 'Home');

        $this->writeSiteConfiguration(
            'acme',
            $site,
            [
                $this->buildDefaultLanguageConfiguration('EN', '/'),
                $this->buildLanguageConfiguration('DE', '/de/', ['EN'], 'strict'),
            ]
        );

        $this->importCSVDataSet(__DIR__ . '/Fixtures/glossary.csv');

        $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)
            ->createFromUserPreferences($GLOBALS['BE_USER']);
        GeneralUtility::makeInstance(DeeplGlossaryService::class)
            ->syncGlossaries(2);
    }

    /**
     * @test
     */
    public function glossaryIsRespectedOnLocalization(): void
    {
        $commandMap = [
            'pages' => [
                4 => [
                    'localize' => 1,
                ],
            ],
            'localization' => [
                'custom' => [
                    'mode' => 'deepl',
                ],
            ],
        ];
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([], $commandMap);
        $dataHandler->process_cmdmap();

        self::assertEmpty($dataHandler->errorLog);
        self::assertCSVDataSet(__DIR__ . '/Fixtures/Results/translateWithGlossary.csv');
    }
}
