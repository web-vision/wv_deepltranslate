<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\ViewHelpers\Be\Access;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use WebVision\Deepltranslate\Core\Access\AllowedTranslateAccess;

final class DeeplTranslateAllowedViewHelper extends AbstractConditionViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
    }

    public static function verdict(array $arguments, RenderingContextInterface $renderingContext): bool
    {
        if (self::getBackendUserAuthentication()->check('custom_options', AllowedTranslateAccess::ALLOWED_TRANSLATE_OPTION_VALUE)) {
            return true;
        }

        return false;
    }

    protected static function getBackendUserAuthentication(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
