<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Functional\Factory;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Factory\GlossaryFactory;
use WebVision\WvDeepltranslate\Tests\Functional\AbstractDeepLTestCase;
use WebVision\WvDeepltranslate\Tests\Functional\Fixtures\Traits\SiteBasedTestTrait;

class GlossaryFactoryTest extends AbstractDeepLTestCase
{
    use SiteBasedTestTrait;
    use ArraySubsetAsserts;

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
        // de
        'DE' => [
            'id' => 1,
            'title' => 'Deutsch',
            'locale' => 'de_DE',
            'hrefLang' => 'de-DE',
            'navigationTitle' => 'Deutsch',
            'flag' => 'de',
            'custom' => [
                'deeplTargetLanguage' => 'DE',
            ],
        ],
        // pl
        'PL' => [
            'id' => 2,
            'title' => 'Polish',
            'locale' => 'pl_PL',
            'hrefLang' => 'pl-PL',
            'navigationTitle' => 'Polski',
            'flag' => 'pl',
            'custom' => [
                'deeplTargetLanguage' => 'PL',
            ],
        ],
        // es
        'ES' => [
            'id' => 3,
            'title' => 'Spanish',
            'locale' => 'es_ES',
            'hrefLang' => 'es-ES',
            'navigationTitle' => 'Español',
            'flag' => 'es',
            'custom' => [
                'deeplTargetLanguage' => 'ES',
            ],
        ],
        // fr
        'FR' => [
            'id' => 4,
            'title' => 'French',
            'locale' => 'fr_FR',
            'hrefLang' => 'de-DE',
            'navigationTitle' => 'Français',
            'flag' => 'fr',
            'custom' => [
                'deeplTargetLanguage' => 'FR',
            ],
        ],
        // it
        'IT' => [
            'id' => 5,
            'title' => 'Italian',
            'locale' => 'it_IT',
            'hrefLang' => 'it-IT',
            'navigationTitle' => 'Italiano',
            'flag' => 'it',
            'custom' => [
                'deeplTargetLanguage' => 'IT',
            ],
        ],
        // nl
        'NL' => [
            'id' => 6,
            'title' => 'Dutch',
            'locale' => 'nl_NL',
            'hrefLang' => 'nl-NL',
            'navigationTitle' => 'Nederlands',
            'flag' => 'nl',
            'custom' => [
                'deeplTargetLanguage' => 'IT',
            ],
        ],
    ];

    protected function setUp(): void
    {
        $this->configurationToUseInTestInstance = array_merge(
            $this->configurationToUseInTestInstance,
            require __DIR__ . '/../Fixtures/ExtensionConfig.php'
        );

        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/Pages.csv');

        $this->writeSiteConfiguration(
            'acme',
            $this->buildSiteConfiguration(1, '/', 'Home'),
            [
                $this->buildDefaultLanguageConfiguration('EN', '/'),
                $this->buildLanguageConfiguration('DE', '/de/', ['EN'], 'strict'),
                $this->buildLanguageConfiguration('PL', '/pl/', ['EN'], 'strict'),
                $this->buildLanguageConfiguration('ES', '/es/', ['EN'], 'strict'),
                $this->buildLanguageConfiguration('FR', '/fr/', ['EN'], 'strict'),
                $this->buildLanguageConfiguration('IT', '/it/', ['EN'], 'strict'),
                $this->buildLanguageConfiguration('NL', '/nl/', ['EN'], 'strict'),
            ]
        );
        $this->setUpFrontendRootPage(1, [], []);

        $this->importCSVDataSet(__DIR__ . '/Fixtures/Glossary.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/BackendUser.csv');
        $this->setUpBackendUser(2);
    }

    /**
     * @test
     */
    public function createGlossaryInformation(): void
    {
        /** @var GlossaryFactory $subject */
        $subject = GeneralUtility::makeInstance(GlossaryFactory::class);
        $glossaries = $subject->createGlossaryInformation(5);

        static::assertCount(8, $glossaries);
        $glossary = $glossaries[0];
        static::assertArraySubset($glossary, [
            'glossary_name' => 'DeepL-Glossary: de => fr',
            'glossary_id' => '',
            'glossary_lastsync' => 0,
            'glossary_ready' => 0,
            'source_lang' => 'de',
            'target_lang' => 'fr',
            'uid' => 1,
            'entries' => [
                0 => [
                    'source' => 'Hallo Welt Deutsch',
                    'target' => 'Hello World French',
                ],
            ],
        ]);
    }

    public static function glossaryLanguageCollectionDataProvider(): \Generator
    {
        yield 'default language has 6 glossary' => [
            'en',
            ['de', 'es', 'fr', 'it', 'nl', 'pl'],
            6,
        ];
        yield 'language de has 1 glossary' => [
            'sourceLang' => 'de',
            'targetLang' => ['fr'],
            'exceptedGlossaryCount' => 1,
        ];
        yield 'language en has 0 glossary' => [
            'sourceLang' => 'en',
            'targetLang' => [],
            'exceptedGlossaryCount' => 0,
        ];
        yield 'language fr has 1 glossary' => [
            'sourceLang' => 'fr',
            'targetLang' => ['de'],
            'exceptedGlossaryCount' => 1,
        ];
        yield 'language it has 0 glossary, because not supported (about mock server)' => [
            'sourceLang' => 'it',
            'targetLang' => [],
            'exceptedGlossaryCount' => 0,
        ];
        yield 'language nl has 0 glossary, because not supported (about mock server)' => [
            'sourceLang' => 'nl',
            'targetLang' => [],
            'exceptedGlossaryCount' => 0,
        ];
        yield 'language pl has 0 glossary, because not supported (about mock server)' => [
            'sourceLang' => 'pl',
            'targetLang' => [],
            'exceptedGlossaryCount' => 0,
        ];
    }

    /**
     * @test
     * @dataProvider glossaryLanguageCollectionDataProvider
     */
    public function glossaryInformationCountOfExpectedMatches(string $sourceLang, array $targetLang, int $exceptedGlossaryCount): void
    {
        /** @var GlossaryFactory $subject */
        $subject = GeneralUtility::makeInstance(GlossaryFactory::class);
        $glossaries = $subject->createGlossaryInformation(5);

        $filteredGlossary = array_filter($glossaries, function (array $glossary) use ($sourceLang, $targetLang) {
            return $glossary['source_lang'] === $sourceLang && in_array($glossary['target_lang'], $targetLang);
        });

        static::assertCount($exceptedGlossaryCount, $filteredGlossary);
    }
}
