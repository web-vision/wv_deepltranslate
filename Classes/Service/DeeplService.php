<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Service;

use DeepL\DeepLException;
use DeepL\Language;
use DeepL\TextResult;
use Doctrine\DBAL\Driver\Exception;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Client;
use WebVision\WvDeepltranslate\Domain\Repository\GlossaryRepository;
use WebVision\WvDeepltranslate\Utility\DeeplBackendUtility;

final class DeeplService
{
    /**
     * Default supported languages
     *
     * @see https://www.deepl.com/de/docs-api/translating-text/#request
     * @var array{source: Language[], target: Language[]}
     */
    public array $apiSupportedLanguages =  [
        'source' => [],
        'target' => [],
    ];

    protected GlossaryRepository $glossaryRepository;

    private FrontendInterface $cache;

    private Client $client;

    private LoggerInterface $logger;

    public function __construct(
        FrontendInterface $cache,
        Client $client,
        GlossaryRepository $glossaryRepository,
        LoggerInterface $logger
    ) {
        $this->cache = $cache;
        $this->client = $client;
        $this->glossaryRepository = $glossaryRepository;
        $this->logger = $logger;

        $this->loadSupportedLanguages();
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
            if ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12) {
                $severity = \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::INFO;
            } else {
                $severity = \TYPO3\CMS\Core\Messaging\AbstractMessage::INFO;
            }
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                'Translation not successful',
                '',
                $severity
            );
            GeneralUtility::makeInstance(FlashMessageService::class)
                ->getMessageQueueByIdentifier()
                ->addMessage($flashMessage);
        }

        return $response;
    }

    public function detectTargetLanguage(string $language): ?Language
    {
        /** @var Language $targetLanguage */
        foreach ($this->apiSupportedLanguages['target'] as $targetLanguage) {
            if ($targetLanguage->code === $language) {
                return $targetLanguage;
            }
        }
        return null;
    }

    public function detectSourceLanguage(string $language): ?Language
    {
        /** @var Language $sourceLanguage */
        foreach ($this->apiSupportedLanguages['source'] as $sourceLanguage) {
            if ($sourceLanguage->code === $language) {
                return $sourceLanguage;
            }
        }
        return null;
    }

    private function loadSupportedLanguages(): void
    {
        $cacheIdentifier = 'wv-deepl-supported-languages-target';
        if (($supportedTargetLanguages = $this->cache->get($cacheIdentifier)) === false) {
            $supportedTargetLanguages = $this->loadSupportedLanguagesFromAPI();

            $this->cache->set($cacheIdentifier, $supportedTargetLanguages, [], 86400);
        }
        $this->apiSupportedLanguages['target'] = $supportedTargetLanguages;

        $cacheIdentifier = 'wv-deepl-supported-languages-source';

        if (($supportedSourceLanguages = $this->cache->get($cacheIdentifier)) === false) {
            $supportedSourceLanguages = $this->loadSupportedLanguagesFromAPI('source');

            $this->cache->set($cacheIdentifier, $supportedSourceLanguages, [], 86400);
        }
        $this->apiSupportedLanguages['source'] = $supportedSourceLanguages;
    }

    /**
     * @return Language[]
     */
    private function loadSupportedLanguagesFromAPI(string $type = 'target'): array
    {
        return $this->client->getSupportedLanguageByType($type);
    }
}
