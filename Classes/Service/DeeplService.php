<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Service;

use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Http\Request;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use WebVision\WvDeepltranslate\Client;
use WebVision\WvDeepltranslate\Domain\Repository\GlossaryRepository;
use WebVision\WvDeepltranslate\Domain\Repository\SettingsRepository;
use WebVision\WvDeepltranslate\Utility\DeeplBackendUtility;

class DeeplService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Default supported languages
     *
     * @see https://www.deepl.com/de/docs-api/translating-text/#request
     * @var string[]
     */
    public array $apiSupportedLanguages =  [
        'source' => [],
        'target' => [],
    ];

    /**
     * Formality supported languages
     * @var string[]
     */
    public array $formalitySupportedLanguages = [];

    protected SettingsRepository $deeplSettingsRepository;

    protected GlossaryRepository $glossaryRepository;

    private FrontendInterface $cache;

    private Client $client;

    public function __construct(
        ?FrontendInterface $cache = null,
        ?Client $client = null
    ) {
        $this->cache = $cache ?? GeneralUtility::makeInstance(CacheManager::class)->getCache('wvdeepltranslate');
        $this->client = $client ?? GeneralUtility::makeInstance(Client::class);
        $this->glossaryRepository = GeneralUtility::makeInstance(GlossaryRepository::class);

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->deeplSettingsRepository = $objectManager->get(SettingsRepository::class);

        if (DeeplBackendUtility::isDeeplApiKeySet()) {
            $this->loadSupportedLanguages();
            $this->apiSupportedLanguages['target'] = $this->deeplSettingsRepository->getSupportedLanguages($this->apiSupportedLanguages['target']);
        }
    }

    /**
     * Deepl Api Call for retrieving translation.
     * @return array<int|string, mixed>
     */
    public function translateRequest(string $content, string $targetLanguage, string $sourceLanguage): array
    {
        // If the source language is set to Autodetect, no glossary can be detected.
        if ($sourceLanguage === 'auto') {
            $sourceLanguage = '';
            $glossary['glossary_id'] = '';
        } else {
            // TODO make glossary findable by current site
            $glossary = $this->glossaryRepository->getGlossaryBySourceAndTarget(
                $sourceLanguage,
                $targetLanguage,
                DeeplBackendUtility::detectCurrentPage()
            );
        }

        try {
            if(!isset($glossary['glossary_id'])) {
                $glossary['glossary_id'] = '';
            }
            $this->logger->debug('Request: ' . print_r([$content, $sourceLanguage, $targetLanguage, $glossary['glossary_id']], true));
            $response = $this->client->translate($content, $sourceLanguage, $targetLanguage, $glossary['glossary_id']);
            $response = json_decode($response->getBody()->getContents(), true);
            $this->logger->debug('Response: ' . print_r($response, true));
        } catch (ClientException $e) {
            $this->logger->error($e->getMessage());
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $e->getMessage(),
                '',
                FlashMessage::INFO
            );
            GeneralUtility::makeInstance(FlashMessageService::class)
                ->getMessageQueueByIdentifier()
                ->addMessage($flashMessage);

            return [];
        }

        return $response;
    }

    private function loadSupportedLanguages(): void
    {
        $cacheIdentifier = 'wv-deepl-supported-languages-target';
        if (($supportedTargetLanguages = $this->cache->get($cacheIdentifier)) === false) {
            $supportedTargetLanguages = $this->loadSupportedLanguagesFromAPI();

            $this->cache->set($cacheIdentifier, $supportedTargetLanguages, [], 86400);
        }

        foreach ($supportedTargetLanguages as $supportedLanguage) {
            $this->apiSupportedLanguages['target'][] = $supportedLanguage['language'];
            if ($supportedLanguage['supports_formality'] === true) {
                $this->formalitySupportedLanguages[] = $supportedLanguage['language'];
            }
        }

        $cacheIdentifier = 'wv-deepl-supported-languages-source';

        if (($supportedSourceLanguages = $this->cache->get($cacheIdentifier)) === false) {
            $supportedSourceLanguages = $this->loadSupportedLanguagesFromAPI('source');

            $this->cache->set($cacheIdentifier, $supportedSourceLanguages, [], 86400);
        }

        foreach ($supportedSourceLanguages as $supportedLanguage) {
            $this->apiSupportedLanguages['source'][] = $supportedLanguage['language'];
        }
    }

    private function loadSupportedLanguagesFromAPI(string $type = 'target'): array
    {
        try {
            $response = $this->client->getSupportedTargetLanguage($type);
        } catch (ClientException $e) {
            return [];
        }

        return json_decode($response->getBody()->getContents(), true);
    }
}
