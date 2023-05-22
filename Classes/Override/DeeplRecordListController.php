<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Override;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Recordlist\Controller\RecordListController;
use WebVision\WvDeepltranslate\Service\DeeplGlossaryService;
use WebVision\WvDeepltranslate\Utility\DeeplBackendUtility;

class DeeplRecordListController extends RecordListController
{
    /**
     * @param string $requestUri
     */
    protected function languageSelector($requestUri): string
    {
        if ($this->pageInfo['module'] === 'glossary') {
            return $this->buildGlossaryTranslationOptionDropdown($requestUri);
        }
        $originalOutput = parent::languageSelector($requestUri);

        if ($originalOutput == '') {
            return $originalOutput;
        }

        if (!DeeplBackendUtility::isDeeplApiKeySet()) {
            return $originalOutput;
        }

        $options = DeeplBackendUtility::buildTranslateDropdown(
            $this->siteLanguages,
            $this->id,
            $requestUri
        );

        if ($options == '') {
            return $originalOutput;
        }

        return str_replace(
            '<div class="col-auto">',
            '<div class="col-auto row"><div class="col-sm-6">',
            $originalOutput
        )
            . '<div class="col-sm-6">'
            . '<select class="form-select" name="createNewLanguage" data-global-event="change" data-action-navigate="$value">'
            . $options
            . '</select>'
            . '</div>'
            . '</div>';
    }

    private function buildGlossaryTranslationOptionDropdown(string $requestUri): string
    {
        if (!$this->getBackendUserAuthentication()->check('tables_modify', 'pages')) {
            return '';
        }

        $glossaryService = GeneralUtility::makeInstance(DeeplGlossaryService::class);
        $possiblePairs = $glossaryService->getPossibleGlossaryLanguageConfig();
        $site = GeneralUtility::makeInstance(SiteFinder::class)
            ->getSiteByPageId($this->id);
        $defaultLanguageIsoCode = $site->getDefaultLanguage()->getTwoLetterIsoCode();

        $possibleGlossaryEntryLanguages = $possiblePairs[$defaultLanguageIsoCode] ?? [];

        $availableTranslations = [];
        foreach ($this->siteLanguages as $siteLanguage) {
            if ($siteLanguage->getLanguageId() === 0) {
                continue;
            }
            if (in_array($siteLanguage->getTwoLetterIsoCode(), $possibleGlossaryEntryLanguages)) {
                $availableTranslations[$siteLanguage->getLanguageId()] = $siteLanguage->getTitle();
            }
        }

        /**
         * code copied from RecordListController
         * @see RecordListController::languageSelector()
         */
        // Then, subtract the languages which are already on the page:
        $localizationParentField = $GLOBALS['TCA']['pages']['ctrl']['transOrigPointerField'];
        $languageField = $GLOBALS['TCA']['pages']['ctrl']['languageField'];
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
            ->add(GeneralUtility::makeInstance(WorkspaceRestriction::class, (int)$this->getBackendUserAuthentication()->workspace));
        $statement = $queryBuilder->select('uid', $languageField)
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq(
                    $localizationParentField,
                    $queryBuilder->createNamedParameter($this->id, Connection::PARAM_INT)
                )
            )
            ->execute();
        while ($pageTranslation = $statement->fetch()) {
            unset($availableTranslations[(int)$pageTranslation[$languageField]]);
        }
        // If any languages are left, make selector:
        if (empty($availableTranslations)) {
            return '';
        }
        $output = '<option value="">' . htmlspecialchars(
            (string)LocalizationUtility::translate(
                'pages.glossary.translate',
                'wv_deepltranslate'
            )
        ) . '</option>';

        /**
         * code copied from RecordListController
         * @see RecordListController::languageSelector()
         */
        foreach ($availableTranslations as $languageUid => $languageTitle) {
            // Build localize command URL to DataHandler (tce_db)
            // which redirects to FormEngine (record_edit)
            // which, when finished editing should return back to the current page (returnUrl)
            $parameters = [
                'justLocalized' => 'pages:' . $this->id . ':' . $languageUid,
                'returnUrl' => $requestUri,
            ];
            $redirectUrl = (string)$this->uriBuilder->buildUriFromRoute('record_edit', $parameters);
            $params = [];
            $params['redirect'] = $redirectUrl;
            $params['cmd']['pages'][$this->id]['localize'] = $languageUid;
            $targetUrl = (string)$this->uriBuilder->buildUriFromRoute('tce_db', $params);
            $output .= '<option value="' . htmlspecialchars($targetUrl) . '">' . htmlspecialchars($languageTitle) . '</option>';
        }

        return '<div class="col-auto">'
            . '<select class="form-select" name="createNewLanguage" data-global-event="change" data-action-navigate="$value">'
            . $output
            . '</select></div>';
    }
}
