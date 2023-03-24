<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Service;

use GuzzleHttp\Exception\BadResponseException;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Exception\GlossaryEntriesNotExistException;
use WebVision\WvDeepltranslate\Service\Client\Client;
use WebVision\WvDeepltranslate\Service\Client\ClientInterface;
use WebVision\WvDeepltranslate\Service\Client\DeepLException;

class DeeplGlossaryService
{
    /**
     * URL Suffix: glossaries
     */
    public const API_URL_SUFFIX_GLOSSARIES = 'glossaries';

    /**
     * URL Suffix: glossary-language-pairs
     */
    public const API_URL_SUFFIX_GLOSSARIES_LANG_PAIRS = 'glossary-language-pairs';

    /**
     * API Version:
     */
    public const API_VERSION = '2';

    private const GLOSSARY_FORMAT = 'tsv';

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var string
     */
    protected string $apiKey;

    /**
     * @var string
     */
    protected string $apiUrl;

    private FrontendInterface $cache;

    public function __construct(
        ?FrontendInterface $cache = null
    ) {
        $this->cache = $cache ?? GeneralUtility::makeInstance(CacheManager::class)->getCache('wvdeepltranslate');

        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('wv_deepltranslate');
        $this->apiKey = $extensionConfiguration['apiKey'];
        $this->apiUrl = $extensionConfiguration['apiUrl'];
        $this->apiUrl = parse_url($this->apiUrl, PHP_URL_HOST); // @TODO - Remove this line when we get only the host from ext config

        $this->client = $client ?? GeneralUtility::makeInstance(
            Client::class,
            $this->apiKey,
            self::API_VERSION,
            $this->apiUrl
        );
    }

    /**
     * Calls the glossary-Endpoint and return Json-response as an array
     *
     * @return array
     *
     * @throws DeepLException
     */
    public function listLanguagePairs()
    {
        return $this->client->request($this->client->buildBaseUrl(self::API_URL_SUFFIX_GLOSSARIES_LANG_PAIRS), '', 'GET');
    }

    /**
     * Calls the glossary-Endpoint and return Json-response as an array
     *
     * @return array
     *
     * @throws DeepLException
     */
    public function listGlossaries()
    {
        return $this->client->request($this->client->buildBaseUrl(self::API_URL_SUFFIX_GLOSSARIES), '', 'GET');
    }

    /**
     * Creates a glossary, entries must be formatted as [sourceText => entryText] e.g: ['Hallo' => 'Hello']
     *
     * @param string $name
     * @param array $entries
     * @param string $sourceLang
     * @param string $targetLang
     *
     * @return array{
     *     glossary_id: string,
     *     name: string,
     *     ready: bool,
     *     source_lang: string,
     *     target_lang: string,
     *     creation_time: string,
     *     entry_count: int
     * }
     *
     * @throws DeepLException
     */
    public function createGlossary(
        string $name,
        array $entries,
        string $sourceLang = 'de',
        string $targetLang = 'en'
    ) {
        if (empty($entries)) {
            throw new GlossaryEntriesNotExistException(
                'Glossary Entries are required',
                1677169192
            );
        }

        $formattedEntries = [];
        foreach ($entries as $entry) {
            $formattedEntries[] = sprintf("%s\t%s", trim($entry['source']), trim($entry['target']));
        }

        $paramsArray = [
            'name' => $name,
            'source_lang'    => $sourceLang,
            'target_lang'    => $targetLang,
            'entries'        => implode("\n", $formattedEntries),
            'entries_format' => self::GLOSSARY_FORMAT,
        ];

        $url  = $this->client->buildBaseUrl(self::API_URL_SUFFIX_GLOSSARIES);
        $body = $this->client->buildQuery($paramsArray);

        return $this->client->request($url, $body);
    }

    /**
     * Deletes a glossary
     *
     * @param string $glossaryId
     *
     * @return array|null
     *
     * @throws DeepLException
     */
    public function deleteGlossary(string $glossaryId)
    {
        $url = $this->client->buildBaseUrl(self::API_URL_SUFFIX_GLOSSARIES);
        $url .= "/$glossaryId";

        try {
            $this->client->request($url, '', 'DELETE');
        } catch (BadResponseException $e) {
            // FlashMessage($message, $title, $severity = self::OK, $storeInSession)
            $message = GeneralUtility::makeInstance(
                FlashMessage::class,
                $e->getMessage(),
                'DeepL Api',
                FlashMessage::WARNING,
                true
            );
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
            $messageQueue->addMessage($message);
        }
    }

    /**
     * Gets information about a glossary
     *
     * @param string $glossaryId
     *
     * @return array|null
     *
     * @throws DeepLException
     */
    public function glossaryInformation(string $glossaryId)
    {
        $url  = $this->client->buildBaseUrl(self::API_URL_SUFFIX_GLOSSARIES);
        $url .= "/$glossaryId";

        return $this->client->request($url, '', 'GET');
    }

    /**
     * Fetch glossary entries and format them as associative array [source => target]
     *
     * @param string $glossaryId
     *
     * @return array
     *
     * @throws DeepLException
     */
    public function glossaryEntries(string $glossaryId)
    {
        $url = $this->client->buildBaseUrl(self::API_URL_SUFFIX_GLOSSARIES);
        $url .= "/$glossaryId/entries";

        $response = $this->client->request($url, '', 'GET');

        $entries = [];
        if (!empty($response)) {
            $allEntries = explode("\n", $response);
            foreach ($allEntries as $entry) {
                $sourceAndTarget = preg_split('/\s+/', rtrim($entry));
                if (isset($sourceAndTarget[0], $sourceAndTarget[1])) {
                    $entries[$sourceAndTarget[0]] = $sourceAndTarget[1];
                }
            }
        }

        return $entries;
    }

    public function getPossibleGlossaryLanguageConfig(): array
    {
        $cacheIdentifier = 'wv-deepl-glossary-pairs';
        if (($pairMappingArray = $this->cache->get($cacheIdentifier)) !== false) {
            return $pairMappingArray;
        }

        $possiblePairs = $this->listLanguagePairs();

        $pairMappingArray = [];
        foreach ($possiblePairs['supported_languages'] as $possiblePair) {
            $pairMappingArray[$possiblePair['source_lang']][] = $possiblePair['target_lang'];
        }

        $this->cache->set($cacheIdentifier, $pairMappingArray);

        return $pairMappingArray;
    }
}
