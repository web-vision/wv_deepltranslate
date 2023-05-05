<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Service;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Client;
use WebVision\WvDeepltranslate\Domain\Repository\GlossaryRepository;
use WebVision\WvDeepltranslate\Exception\GlossaryEntriesNotExistException;

class DeeplGlossaryService
{
    private Client $client;

    private FrontendInterface $cache;

    protected GlossaryRepository $glossaryRepository;

    public function __construct(
        ?FrontendInterface $cache = null,
        ?Client $client = null,
        ?GlossaryRepository $glossaryRepository = null
    ) {
        $this->cache = $cache ?? GeneralUtility::makeInstance(CacheManager::class)->getCache('wvdeepltranslate');
        $this->client = $client ?? GeneralUtility::makeInstance(Client::class);
        $this->glossaryRepository = $glossaryRepository ?? GeneralUtility::makeInstance(GlossaryRepository::class);
    }

    /**
     * Calls the glossary-Endpoint and return Json-response as an array
     *
     * @return array
     * @throws DeepLException
     */
    public function listLanguagePairs(): array
    {
        $response =  $this->client->getGlossaryLanguagePairs();

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Calls the glossary-Endpoint and return Json-response as an array
     *
     * @return array
     * @throws DeepLException
     */
    public function listGlossaries(): array
    {
        $response = $this->client->getAllGlossaries();

        return json_decode($response->getBody()->getContents(), true);
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
     * @throws GlossaryEntriesNotExistException
     */
    public function createGlossary(
        string $name,
        array $entries,
        string $sourceLang = 'de',
        string $targetLang = 'en'
    ): array {
        if (empty($entries)) {
            throw new GlossaryEntriesNotExistException(
                'Glossary Entries are required',
                1677169192
            );
        }

        $response = $this->client->createGlossary($name, $sourceLang, $targetLang, $entries);

        return json_decode($response->getBody()->getContents(), true);
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
    public function deleteGlossary(string $glossaryId): ?array
    {
        try {
            $this->client->deleteGlossary($glossaryId);
        } catch (BadResponseException $e) {
            // FlashMessage($message, $title, $severity = self::OK, $storeInSession)
            if (Environment::isCli()) {
                throw $e;
            }
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

        return null;
    }

    /**
     * Gets information about a glossary
     *
     * @param string $glossaryId
     * @return array|null
     *
     * @throws DeepLException
     */
    public function glossaryInformation(string $glossaryId): ?array
    {
        $response = $this->client->getGlossary($glossaryId);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Fetch glossary entries and format them as associative array [source => target]
     *
     * @param string $glossaryId
     * @return array
     * @throws DeepLException
     */
    public function glossaryEntries(string $glossaryId): array
    {
        $response = $this->client->getGlossaryEntries($glossaryId);

        $jsons = json_decode($response->getBody()->getContents(), true);

        $entries = [];
        if (!empty($response)) {
            $allEntries = explode("\n", $jsons);
            foreach ($allEntries as $entry) {
                $sourceAndTarget = preg_split('/\s+/', rtrim($entry));
                if (isset($sourceAndTarget[0], $sourceAndTarget[1])) {
                    $entries[$sourceAndTarget[0]] = $sourceAndTarget[1];
                }
            }
        }

        return $entries;
    }

    /**
     * @throws DeepLException
     */
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

    /**
     * @throws DeepLException
     * @throws SiteNotFoundException
     */
    public function syncGlossaries(int $uid): void
    {
        $glossaries = $this->glossaryRepository
            ->getGlossaryInformationForSync($uid);

        foreach ($glossaries as $glossaryInformation) {
            if ($glossaryInformation['glossary_id'] !== '') {
                try {
                    $this->deleteGlossary($glossaryInformation['glossary_id']);
                } catch (ClientException $e) {
                }
            }

            try {
                $glossary = $this->createGlossary(
                    $glossaryInformation['glossary_name'],
                    $glossaryInformation['entries'],
                    $glossaryInformation['source_lang'],
                    $glossaryInformation['target_lang']
                );
            } catch (GlossaryEntriesNotExistException $exception) {
                $glossary = [];
            }

            $this->glossaryRepository->updateLocalGlossary(
                $glossary,
                (int)$glossaryInformation['uid']
            );
        }
    }
}
