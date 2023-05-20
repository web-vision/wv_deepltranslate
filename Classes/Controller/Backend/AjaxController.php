<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Controller\Backend;

use Psr\Http\Message\ServerRequestInterface;

use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Configuration;

// @todo use #[Controller] if 12.4+ only support is established, see: TYPO3\CMS\Backend\Attribute\Controller;
final class AjaxController
{
    private Configuration $configuration;

    public function __construct()
    {
        // @todo Consider to make this handed over through DI.
        $this->configuration = GeneralUtility::makeInstance(Configuration::class);
    }

    /**
     * check deepl Settings (url,apikey).
     */
    public function checkExtensionConfiguration(ServerRequestInterface $request): JsonResponse
    {
        $configurationStatus = [
            'status' => true,
            'message' => '',
        ];
        if ($this->configuration->getApiKey() == null
            && $this->configuration->getApiUrl() == null
        ) {
            $configurationStatus['status']  = false;
            $configurationStatus['message'] = 'Deepl settings not enabled';
        }

        return new JsonResponse($configurationStatus);
    }
}
