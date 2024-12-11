<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\ViewHelpers\Be;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * condition ViewHelper for getting information about installed packages
 * Usage:
 * <deepl:be.extensionActive extension="enable_translated_content">
 *     <f:then>
 *         <!-- do stuff -->
 *     </f:then>
 *     <f:else>
 *         <!-- do other stuff -->
 *     </f:else>
 * </deepl:be.extensionActive>
 *
 * Inline example:
 * {deepl:be.extensionActive(extension: 'enable_translated_content', then: '', else: '')}
 */
final class ExtensionActiveViewHelper extends AbstractConditionViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('extension', 'string', 'The extension to check', true);
    }

    public static function verdict(array $arguments, RenderingContextInterface $renderingContext): bool
    {
        if (ExtensionManagementUtility::isLoaded((string)($arguments['extension'] ?? ''))) {
            return true;
        }
        return false;
    }
}
