<?php

declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Override;

use TYPO3\CMS\Recordlist\Controller\RecordListController;
use WebVision\WvDeepltranslate\Utility\DeeplBackendUtility;

class DeeplRecordListController extends RecordListController
{
    /**
     * @param string $requestUri
     */
    protected function languageSelector($requestUri): string
    {
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
            return '';
        }
        return str_replace('<div class="col-auto">', '<div class="col-auto row"><div class="col-sm-6">', $originalOutput)
            . '<div class="col-sm-6 row">'
            . '<label class="col-sm-4">Translate with DeepL</label>'
            . '<div class="col-sm-8">'
            . '<select class="form-select" name="createNewLanguage" data-global-event="change" data-action-navigate="$value">'
            . $options
            . '</select></div></div></div>';
    }
}
