<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Service;

use DateTime;
use DeepL\DeepLException;
use DeepL\GlossaryEntries;
use DeepL\GlossaryInfo;
use DeepL\GlossaryLanguagePair;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Client;
use WebVision\WvDeepltranslate\Domain\Repository\GlossaryRepository;
use WebVision\WvDeepltranslate\Exception\GlossaryEntriesNotExistException;

final class DeeplGlossaryService
{
    private Client $client;

    private FrontendInterface $cache;

    protected GlossaryRepository $glossaryRepository;

    public function __construct(
        FrontendInterface $cache,
        Client $client,
        GlossaryRepository $glossaryRepository
    ) {
        $this->cache = $cache;
        $this->client = $client;
        $this->glossaryRepository = $glossaryRepository;
    }

    /**
     * Calls the glossary-Endpoint and return Json-response as an array
     *
     * @return GlossaryLanguagePair[]
     * @throws DeepLException
     */
    public function listLanguagePairs(): array
    {
        return $this->client->getGlossaryLanguagePairs();
    }

    /**
     * Calls the glossary-Endpoint and return Json-response as an array
     *
     * @return GlossaryInfo[]
     * @throws DeepLException
     */
    public function listGlossaries(): array
    {
        return $this->client->getAllGlossaries();
    }

    /**
     * Creates a glossary, entries must be formatted as [sourceText => entryText] e.g: ['Hallo' => 'Hello']
     *
     * @param array<int, array{source: string, target: string}> $entries
     *
     * @throws GlossaryEntriesNotExistException
     * @throws DeepLException
     */
    public function createGlossary(
        string $name,
        array $entries,
        string $sourceLang = 'de',
        string $targetLang = 'en'
    ): GlossaryInfo {
        if (empty($entries)) {
            throw new GlossaryEntriesNotExistException(
                'Glossary Entries are required',
                1677169192
            );
        }

        return $this->client->createGlossary($name, $sourceLang, $targetLang, $entries);
    }

    /**
     * Deletes a glossary
     *
     * @param string $glossaryId
     *
     * @throws DeepLException
     */
    public function deleteGlossary(string $glossaryId): void
    {
        try {
            $this->client->deleteGlossary($glossaryId);
        } catch (DeepLException $e) {
            if (Environment::isCli()) {
                throw $e;
            }
            if ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12) {
                $severity = \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::WARNING;
            } else {
                $severity = \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING;
            }
            $message = GeneralUtility::makeInstance(
                FlashMessage::class,
                $e->getMessage(),
                'DeepL Api',
                $severity,
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
     * @throws DeepLException
     */
    public function glossaryInformation(string $glossaryId): GlossaryInfo
    {
        return $this->client->getGlossary($glossaryId);
    }

    /**
     * Fetch glossary entries and format them as an associative array [source => target]
     *
     * @throws DeepLException
     */
    public function glossaryEntries(string $glossaryId): GlossaryEntries
    {
        return $this->client->getGlossaryEntries($glossaryId);
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
     * @throws Exception
     * @throws SiteNotFoundException
     * @throws DBALException
     * @throws \Doctrine\DBAL\Exception
     */
    public function syncGlossaries(int $uid): void
    {
        $glossaries = $this->glossaryRepository
            ->getGlossaryInformationForSync($uid);

        foreach ($glossaries as $glossaryInformation) {
            if ($glossaryInformation['glossary_id'] !== '') {
                try {
                    $this->deleteGlossary($glossaryInformation['glossary_id']);
                } catch (DeepLException $e) {
                }
            }

            try {
                $glossary = $this->createGlossary(
                    $glossaryInformation['glossary_name'],
                    $glossaryInformation['entries'],
                    $glossaryInformation['source_lang'],
                    $glossaryInformation['target_lang']
                );
            } catch (DeepLException|GlossaryEntriesNotExistException $exception) {
                $glossary = new GlossaryInfo(
                    '',
                    '',
                    false,
                    '',
                    '',
                    new DateTime(),
                    0
                );
            }

            $this->glossaryRepository->updateLocalGlossary(
                $glossary,
                (int)$glossaryInformation['uid']
            );
        }
    }
}
