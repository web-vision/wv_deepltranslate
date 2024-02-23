<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Service;

use DeepL\Language;
use DeepL\TextResult;
use Doctrine\DBAL\Driver\Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\ClientInterface;
use WebVision\WvDeepltranslate\Domain\Repository\GlossaryRepository;
use WebVision\WvDeepltranslate\Exception\ApiKeyNotSetException;
use WebVision\WvDeepltranslate\Utility\DeeplBackendUtility;

final class DeeplService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected GlossaryRepository $glossaryRepository;

    private FrontendInterface $cache;

    private ClientInterface $client;

    public function __construct(
        FrontendInterface $cache,
        ClientInterface $client,
        GlossaryRepository $glossaryRepository
    ) {
        $this->cache = $cache;
        $this->client = $client;
        $this->glossaryRepository = $glossaryRepository;
    }

    /**
     * Deepl Api Call for retrieving translation.
     *
     * @return TextResult|TextResult[]|null
     * @throws Exception
     * @throws SiteNotFoundException
     * @throws \Doctrine\DBAL\Exception
     */
    public function translateRequest(
        string $content,
        string $targetLanguage,
        string $sourceLanguage
    ) {
        // If the source language is set to Autodetect, no glossary can be detected.
        if ($sourceLanguage === 'auto') {
            $sourceLanguage = null;
            $glossary['glossary_id'] = '';
        } else {
            // @todo Make glossary findable by current site.
            $glossary = $this->glossaryRepository->getGlossaryBySourceAndTarget(
                $sourceLanguage,
                $targetLanguage,
                DeeplBackendUtility::detectCurrentPage()
            );
        }

        $response = $this->client->translate($content, $sourceLanguage, $targetLanguage, $glossary['glossary_id']);

        if ($response === null) {
            if (!Environment::isCli() || !Environment::getContext()->isTesting()) {
                $flashMessage = GeneralUtility::makeInstance(
                    FlashMessage::class,
                    'Translation not successful',
                    '',
                    -1
                );
                GeneralUtility::makeInstance(FlashMessageService::class)
                    ->getMessageQueueByIdentifier()
                    ->addMessage($flashMessage);
            }
        }

        return $response;
    }

    public function detectTargetLanguage(string $language): ?Language
    {
        /** @var Language $targetLanguage */
        foreach ($this->getSupportLanguage()['target'] as $targetLanguage) {
            if ($targetLanguage->code === $language) {
                return $targetLanguage;
            }
        }

        return null;
    }

    public function detectSourceLanguage(string $language): ?Language
    {
        /** @var Language $sourceLanguage */
        foreach ($this->getSupportLanguage()['source'] as $sourceLanguage) {
            if ($sourceLanguage->code === $language) {
                return $sourceLanguage;
            }
        }

        return null;
    }

    /**
     * Default supported languages
     *
     * @see https://www.deepl.com/de/docs-api/translating-text/#request
     * @return array{source: Language[], target: Language[]}
     */
    public function getSupportLanguage(): array
    {
        return $this->loadSupportedLanguages();
    }

    /**
     * ToDo: Build own deepl language support object
     *
     * @return array{source: Language[], target: Language[]}
     */
    private function loadSupportedLanguages(): array
    {
        $apiSupportedLanguages = [
            'source' => [],
            'target' => [],
        ];

        $cacheIdentifier = 'wv-deepl-supported-languages-target';
        if (($supportedTargetLanguages = $this->cache->get($cacheIdentifier)) === false) {
            $supportedTargetLanguages = $this->loadSupportedLanguagesFromAPI();

            $this->cache->set($cacheIdentifier, $supportedTargetLanguages, [], 86400);
        }

        $apiSupportedLanguages['target'] = $supportedTargetLanguages;

        $cacheIdentifier = 'wv-deepl-supported-languages-source';

        if (($supportedSourceLanguages = $this->cache->get($cacheIdentifier)) === false) {
            $supportedSourceLanguages = $this->loadSupportedLanguagesFromAPI('source');

            $this->cache->set($cacheIdentifier, $supportedSourceLanguages, [], 86400);
        }

        $apiSupportedLanguages['source'] = $supportedSourceLanguages;

        return $apiSupportedLanguages;
    }

    /**
     * @return Language[]
     */
    private function loadSupportedLanguagesFromAPI(string $type = 'target'): array
    {
        try {
            return $this->client->getSupportedLanguageByType($type);
        } catch (ApiKeyNotSetException $exception) {
            // @todo Can be replaced with `$this->logger?->` when TYPO3 v11 and therefore PHP 7.4/8.0 support is dropped.
            if ($this->logger !== null) {
                $this->logger->error(sprintf('%s (%d)', $exception->getMessage(), $exception->getCode()));
            }
            return [];
        }
    }
}
