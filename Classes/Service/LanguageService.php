<?php

declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Service;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LanguageService
{
    /**
     * @param bool $sourceLang ToDo: need better solution for source lang mode
     *
     * @return array{uid: int, language_isocode: string}
     */
    public function getSiteLanguageConfiguration(
        string $tableName,
        int $currentRecordId,
        int $languageId,
        bool $sourceLang = false
    ): array {
        $currentPageRecord = BackendUtility::getRecord($tableName, $currentRecordId);
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);

        try {
            $site = $siteFinder->getSiteByPageId($currentPageRecord['pid']);
            if (!isset($site->getConfiguration()['languages'])) {
                return [];
            };

            $languages = array_filter($site->getConfiguration()['languages'], function ($value) use ($languageId) {
                if (!is_array($value)) {
                    return false;
                }

                if ((int)$value['languageId'] === $languageId) {
                    return true;
                }

                return false;
            });

            $language = array_shift($languages);

            $languageIsoCode = strtoupper($language['iso-639-1']);
            if ($sourceLang === false) {
                $languageIsoCode = $language['deeplTargetLanguage'];
            }

            return [
                'uid' => $language['languageId'] ?? 0,
                'language_isocode' => $languageIsoCode,
            ];
        } catch (SiteNotFoundException $exception) {
            // Ignore, use defaults
        }

        return [];
    }
}
