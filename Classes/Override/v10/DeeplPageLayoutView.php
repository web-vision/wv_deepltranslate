<?php

declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Override\v10;

use TYPO3\CMS\Backend\View\PageLayoutView;
use WebVision\WvDeepltranslate\Utility\DeeplBackendUtility;

/**
 * @deprecated will be removed in version 4
 */
class DeeplPageLayoutView extends PageLayoutView
{
    public function languageSelector($id)
    {
        $originalOutput = parent::languageSelector($id);

        if ($originalOutput == '') {
            return $originalOutput;
        }
        $options = DeeplBackendUtility::buildTranslateDropdown(
            $this->siteLanguages,
            $this->id,
            $originalOutput
        );
        if ($options == '') {
            return '';
        }
        return str_replace(
            '<div class="row row-cols-auto align-items-end g-3 mb-3"><div class="col">',
            '<div class="col-auto row"><div class="col-sm-6">',
            $originalOutput
        )
            . '<div class="col-sm-6 row">'
            . '<label class="col-sm-4">Translate with DeepL</label>'
            . '<div class="col-sm-8">'
            . '<select class="form-select" name="createNewLanguage" data-global-event="change" data-action-navigate="$value">'
            . $options
            . '</select></div></div></div>';
    }
}
