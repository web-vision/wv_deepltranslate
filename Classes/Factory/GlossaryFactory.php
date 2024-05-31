<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Factory;

use Doctrine\DBAL\Driver\Exception;
use TYPO3\CMS\Backend\Configuration\TranslationConfigurationProvider;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Domain\Repository\GlossaryRepository;
use WebVision\WvDeepltranslate\Service\DeeplGlossaryService;

/**
 * Factory object to create DeepL glossary information from TYPO3 Record/Structure
 * Only glossaries language combination that are supported by the DeepL-API through "/glossary-language-pairs" are created
 */
class GlossaryFactory
{
    private SiteFinder $siteFinder;

    private GlossaryRepository $glossaryRepository;

    private DeeplGlossaryService $deeplGlossaryService;

    public function __construct(
        SiteFinder $siteFinder,
        GlossaryRepository $glossaryRepository,
        DeeplGlossaryService $deeplGlossaryService
    ) {
        $this->siteFinder = $siteFinder;
        $this->glossaryRepository = $glossaryRepository;
        $this->deeplGlossaryService = $deeplGlossaryService;
    }

    /**
     * @param int $pageId
     * @return array<int, array{
     *     glossary_name: string,
     *     uid: int,
     *     glossary_id: string,
     *     source_lang: string,
     *     target_lang: string,
     *     entries: array<int, array{source: string, target: string}>
     * }>
     *
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws SiteNotFoundException
     */
    public function createGlossaryInformation(int $pageId): array
    {
        $glossaries = [];
        $localizationArray = [];

        $page = BackendUtility::getRecord('pages', $pageId, '*');
        if ($page['module'] !== 'glossary') {
            throw new \RuntimeException('', 1716556217634);
        }

        $availableLanguagePairs = $this->deeplGlossaryService->getPossibleGlossaryLanguageConfig();
        $sourceLangIsoCode = $this->getDefaultLanguageCode($pageId);

        $entries = $this->glossaryRepository->getOriginalEntries($pageId);

        $localizationArray[$sourceLangIsoCode] = $entries;

        $localizationLanguageIds = $this->getAvailableLocalizations($pageId);
        // fetch all language information available for building all glossaries
        foreach ($localizationLanguageIds as $localizationLanguageId) {
            $localizedEntries = $this->glossaryRepository->getLocalizedEntries($pageId, $localizationLanguageId);
            $targetLanguageIsoCode = $this->getTargetLanguageCode($pageId, $localizationLanguageId);
            $localizationArray[$targetLanguageIsoCode] = $localizedEntries;
        }

        foreach ($availableLanguagePairs as $sourceLang => $availableTargets) {
            // no entry to possible source in the current page
            if (!isset($localizationArray[$sourceLang])) {
                continue;
            }

            foreach ($availableTargets as $targetLang) {
                // target isn't configured in the current page
                if (!isset($localizationArray[$targetLang])) {
                    continue;
                }

                // target is site default, continue
                if ($targetLang === $sourceLangIsoCode) {
                    continue;
                }

                $glossaryInformation = $this->glossaryRepository->getGlossaryBySourceAndTargetForSync(
                    $sourceLang,
                    $targetLang,
                    $page
                );
                $glossaryInformation['source_lang'] = $sourceLang;
                $glossaryInformation['target_lang'] = $targetLang;

                $entries = [];
                foreach ($localizationArray[$sourceLang] as $entryId => $sourceEntry) {
                    // no source target pair, next
                    if (!isset($localizationArray[$targetLang][$entryId])) {
                        continue;
                    }
                    $entries[] = [
                        'source' => $sourceEntry['term'],
                        'target' => $localizationArray[$targetLang][$entryId]['term'],
                    ];
                }
                // no pairs detected
                if (count($entries) == 0) {
                    continue;
                }

                // remove duplicates
                $sources = [];
                foreach ($entries as $position => $entry) {
                    if (in_array($entry['source'], $sources)) {
                        unset($entries[$position]);
                        continue;
                    }
                    $sources[] = $entry['source'];
                }

                // reset entries keys
                $glossaryInformation['entries'] = array_values($entries);
                $glossaries[] = $glossaryInformation;
            }
        }

        return $glossaries;
    }

    /**
     * @return array<int, mixed>
     */
    private function getAvailableLocalizations(int $pageId): array
    {
        $translations = GeneralUtility::makeInstance(TranslationConfigurationProvider::class)
            ->translationInfo('pages', $pageId);

        // Error string given, if not matching. Return an empty array then
        if (!is_array($translations)) {
            return [];
        }

        $availableTranslations = [];
        foreach ($translations['translations'] as $translation) {
            $availableTranslations[] = $translation['sys_language_uid'];
        }

        return $availableTranslations;
    }

    protected function getTargetLanguageCode(int $pageId, int $languageId): string
    {
        $site = $this->siteFinder->getSiteByPageId($pageId);
        $typo3Version = new Typo3Version();
        if ($typo3Version->getMajorVersion() < 12) {
            $targetLangIsoCode = $site->getLanguageById($languageId)->getTwoLetterIsoCode();
        } else {
            $targetLangIsoCode = $site->getLanguageById($languageId)->getLocale()->getLanguageCode();
        }

        return $targetLangIsoCode;
    }

    private function getDefaultLanguageCode(int $pageId): string
    {
        $site = $this->siteFinder->getSiteByPageId($pageId);
        $typo3Version = new Typo3Version();
        if ($typo3Version->getMajorVersion() < 12) {
            $sourceLangIsoCode = $site->getDefaultLanguage()->getTwoLetterIsoCode();
        } else {
            $sourceLangIsoCode = $site->getDefaultLanguage()->getLocale()->getLanguageCode();
        }
        return $sourceLangIsoCode;
    }
}
