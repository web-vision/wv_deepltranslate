<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate;

use DeepL\Translator;
use DeepL\TranslatorOptions;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Exception\ApiKeyNotSetException;

/**
 * @internal No public usage
 */
abstract class AbstractClient implements ClientInterface
{
    protected ConfigurationInterface $configuration;

    protected ?Translator $translator = null;

    protected LoggerInterface $logger;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Wrapper function to handel ApiKey exception
     */
    protected function getTranslator(): Translator
    {
        if ($this->translator instanceof Translator) {
            return $this->translator;
        }

        if ($this->configuration->getApiKey() === '') {
            throw new ApiKeyNotSetException('The api key ist not set', 1708081233823);
        }

        $options = [];
        if (
            isset($GLOBALS['TYPO3_CONF_VARS']['HTTP']['proxy'])
            && $GLOBALS['TYPO3_CONF_VARS']['HTTP']['proxy'] !== ''
            && GeneralUtility::isValidUrl($GLOBALS['TYPO3_CONF_VARS']['HTTP']['proxy'])
        ) {
            $options[TranslatorOptions::PROXY] = $GLOBALS['TYPO3_CONF_VARS']['HTTP']['proxy'];
        }

        $this->translator = new Translator($this->configuration->getApiKey(), $options);

        return $this->translator;
    }
}
