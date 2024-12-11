<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\ViewHelpers\Be\Access;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Configuration\Features;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use WebVision\WvDeepltranslate\Access\AllowedTranslateAccess;

final class DeeplTranslateAllowedViewHelper extends AbstractConditionViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
    }

    public static function verdict(array $arguments, RenderingContextInterface $renderingContext): bool
    {
        if (
            !GeneralUtility::makeInstance(Features::class)->isFeatureEnabled('deeplTranslationUserConfigured')
             || self::getBackendUserAuthentication()->check('custom_options', AllowedTranslateAccess::ALLOWED_TRANSLATE_OPTION_VALUE)
        ) {
            return true;
        }

        return false;
    }

    protected static function getBackendUserAuthentication(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
