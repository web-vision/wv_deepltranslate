<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\ViewHelpers\Be\Access;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use WebVision\WvDeepltranslate\Access\AllowedTranslateAccess;

class DeeplTranslateAllowedViewHelper extends AbstractConditionViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
    }

    public static function verdict(array $arguments, RenderingContextInterface $renderingContext): bool
    {
        if ($GLOBALS['BE_USER']->check('custom_options', AllowedTranslateAccess::ALLOWED_TRANSLATE_OPTION_VALUE)) {
            return true;
        }
        return false;
    }
}
