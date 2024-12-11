<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Service;

use TYPO3\CMS\Core\Site\Entity\Site;
use WebVision\Deepltranslate\Core\Exception\LanguageIsoCodeNotFoundException;
use WebVision\Deepltranslate\Core\Exception\LanguageRecordNotFoundException;

final class LanguageService
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
        DeeplService $deeplService
    ) {
        $this->deeplService = $deeplService;
    }

    /**
     * @return array{uid: int, title: string, language_isocode: string, languageCode: string}
     */
    public function getSourceLanguage(Site $currentSite): array
    {
        $languageIsoCode = $currentSite->getDefaultLanguage()->getLocale()->getLanguageCode();
        $sourceLanguageRecord = [
            'uid' => $currentSite->getDefaultLanguage()->getLanguageId(),
            'title' => $currentSite->getDefaultLanguage()->getTitle(),
            'language_isocode' => strtoupper($languageIsoCode),
            'languageCode' => strtoupper($languageIsoCode),
        ];

        if (!$this->deeplService->isSourceLanguageSupported($sourceLanguageRecord['language_isocode'])) {
            // When sources language not supported oder not exist set auto detect for deepL API
            $sourceLanguageRecord['title'] = 'auto';
            $sourceLanguageRecord['language_isocode'] = 'auto';
            $sourceLanguageRecord['languageCode'] = 'auto';
        }

        return $sourceLanguageRecord;
    }

    /**
     * @return array{uid: int, title: string, language_isocode: string, languageCode: string, formality: string}
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
        $languageCode = null;

        foreach ($this->possibleLangMatches as $possibleLangMatch) {
            if (!array_key_exists($possibleLangMatch, $language)) {
                continue;
            }

            if (!$this->deeplService->isTargetLanguageSupported(strtoupper($language[$possibleLangMatch]))) {
                continue;
            }

            $languageCode = strtoupper($language[$possibleLangMatch]);
            break;
        }

        if ($languageCode === null) {
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
            'language_isocode' => $languageCode,
            'languageCode' => $languageCode,
            'formality' => $language['deeplFormality'] ?? '',
        ];
    }
}
