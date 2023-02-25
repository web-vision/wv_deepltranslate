<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Override\v10;

use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
        if (!DeeplBackendUtility::isDeeplApiKeySet()) {
            return $originalOutput;
        }

        $options = DeeplBackendUtility::buildTranslateDropdown(
            $this->siteLanguages,
            $this->id,
            GeneralUtility::getIndpEnv('REQUEST_URI')
        );

        if ($options == '') {
            return $originalOutput;
        }

        $originalOutput = str_ireplace('</div></div>', '</div>', $originalOutput);
        return $originalOutput
            . '<div class="form-group">'
            . '<select class="form-control input-sm" onchange="window.location.href=this.options[this.selectedIndex].value">'
            . $options
            . '</select></div></div>';
    }
}
