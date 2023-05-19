<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Service;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Exception\LanguageIsoCodeNotFoundException;
use WebVision\WvDeepltranslate\Exception\LanguageRecordNotFoundException;

class LanguageService
{
    protected DeeplService $deeplService;

    /**
     * @todo TYPO3 v12 do not have hreflang & iso-639-1 directly in the raw language configurations anymore.
     *       @link LanguageService::getTargetLanguage() for additional commets.
     *       See: https://review.typo3.org/c/Packages/TYPO3.CMS/+/77807
     *            https://review.typo3.org/c/Packages/TYPO3.CMS/+/77597
     *            https://review.typo3.org/c/Packages/TYPO3.CMS/+/77726
     *            https://review.typo3.org/c/Packages/TYPO3.CMS/+/77814
     */
    protected array $possibleLangMatches = [
        'deeplTargetLanguage',
        'hreflang',
        'iso-639-1',
    ];

    public function __construct(
        ?DeeplService $deeplService = null
    ) {
        $this->deeplService = $deeplService ?? GeneralUtility::makeInstance(DeeplService::class);
    }

    /**
     * @return array{site: Site, pageUid: int}|null
     */
    public function getCurrentSite(string $tableName, int $currentRecordId): ?array
    {
        if ($tableName === 'pages') {
            $pageId = $currentRecordId;
        } else {
            $currentPageRecord = BackendUtility::getRecord($tableName, $currentRecordId);
            $pageId = (int)$currentPageRecord['pid'];
        }
        try {
            return [
                'site' => GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageId),
                'pageUid' => $pageId,
            ];
        } catch (SiteNotFoundException $e) {
            return null;
        }
    }

    /**
     * @return array{uid: int, title: string, language_isocode: string}
     * @throws LanguageIsoCodeNotFoundException
     */
    public function getSourceLanguage(Site $currentSite): array
    {
        $sourceLanguageRecord = [
            'uid' => $currentSite->getDefaultLanguage()->getLanguageId(),
            'title' => $currentSite->getDefaultLanguage()->getTitle(),
            'language_isocode' => strtoupper($currentSite->getDefaultLanguage()->getTwoLetterIsoCode()),
        ];

        if (!in_array(
            $sourceLanguageRecord['language_isocode'],
            $this->deeplService->apiSupportedLanguages['source']
        )) {
            // When sources language not supported oder not exist set auto detect for deepL API
            $sourceLanguageRecord['title'] = 'auto';
            $sourceLanguageRecord['language_isocode'] = 'auto';
        }

        return $sourceLanguageRecord;
    }

    /**
     * @return array{uid: int, title: string, language_isocode: string}
     * @throws LanguageRecordNotFoundException
     * @throws LanguageIsoCodeNotFoundException
     */
    public function getTargetLanguage(Site $currentSite, int $languageId): array
    {
        // @todo TYPO3 v12 changed locale API and therefore site configuration. Configured languages do no longer
        //       directly contains values like hreflang or iso-639-1 directly. Possible workarounds would be to
        //       operate directly on the siteLanguage objects and no longer use the raw configuration values.
        //       See: https://review.typo3.org/c/Packages/TYPO3.CMS/+/77807
        //            https://review.typo3.org/c/Packages/TYPO3.CMS/+/77597
        //            https://review.typo3.org/c/Packages/TYPO3.CMS/+/77726
        //            https://review.typo3.org/c/Packages/TYPO3.CMS/+/77814
        $languages = array_filter($currentSite->getConfiguration()['languages'], function ($value) use ($languageId) {
            if (!is_array($value)) {
                return false;
            }

            if ((int)$value['languageId'] === $languageId) {
                return true;
            }

            return false;
        });

        if (count($languages) === 0) {
            throw new LanguageRecordNotFoundException(
                sprintf(
                    'Language "%d" not found in SiteConfig "%s"',
                    $languageId,
                    (string)($currentSite->getConfiguration()['websiteTitle'] ?? '')
                ),
                1676824459
            );
        }
        $language = reset($languages);
        $languageIsoCode = null;

        foreach ($this->possibleLangMatches as $possibleLangMatch) {
            if (array_key_exists($possibleLangMatch, $language)
                && in_array(
                    strtoupper($language[$possibleLangMatch]),
                    $this->deeplService->apiSupportedLanguages['target']
                )
            ) {
                $languageIsoCode = strtoupper($language[$possibleLangMatch]);
                break;
            }
        }
        if ($languageIsoCode === null) {
            throw new LanguageIsoCodeNotFoundException(
                sprintf(
                    'No API supported target found for language "%s" in site "%s"',
                    $language['title'],
                    (string)($currentSite->getConfiguration()['websiteTitle'] ?? '')
                ),
                1676741837
            );
        }

        return [
            'uid' => $language['languageId'] ?? 0,
            'title' => $language['title'],
            'language_isocode' => $languageIsoCode,
        ];
    }
}
