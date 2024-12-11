<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Service;

use DateTime;
use DeepL\GlossaryEntries;
use DeepL\GlossaryInfo;
use DeepL\GlossaryLanguagePair;
use Doctrine\DBAL\Driver\Exception;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use WebVision\Deepltranslate\Core\ClientInterface;
use WebVision\Deepltranslate\Core\Domain\Repository\GlossaryRepository;
use WebVision\Deepltranslate\Core\Exception\FailedToCreateGlossaryException;
use WebVision\Deepltranslate\Core\Exception\GlossaryEntriesNotExistException;

final class DeeplGlossaryService
{
    private ClientInterface $client;

    private FrontendInterface $cache;

    protected GlossaryRepository $glossaryRepository;

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
     * Calls the glossary-Endpoint and return Json-response as an array
     *
     * @return GlossaryLanguagePair[]
     */
    public function listLanguagePairs(): array
    {
        return $this->client->getGlossaryLanguagePairs();
    }

    /**
     * Calls the glossary-Endpoint and return Json-response as an array
     *
     * @return GlossaryInfo[]
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
     */
    public function deleteGlossary(string $glossaryId): void
    {
        $this->client->deleteGlossary($glossaryId);
    }

    /**
     * Gets information about a glossary
     */
    public function glossaryInformation(string $glossaryId): ?GlossaryInfo
    {
        return $this->client->getGlossary($glossaryId);
    }

    /**
     * Fetch glossary entries and format them as an associative array [source => target]
     */
    public function glossaryEntries(string $glossaryId): ?GlossaryEntries
    {
        return $this->client->getGlossaryEntries($glossaryId);
    }

    public function getPossibleGlossaryLanguageConfig(): array
    {
        $cacheIdentifier = 'wv-deepl-glossary-pairs';
        if (($pairMappingArray = $this->cache->get($cacheIdentifier)) !== false) {
            return $pairMappingArray;
        }

        $possiblePairs = $this->listLanguagePairs();

        $pairMappingArray = [];
        foreach ($possiblePairs as $possiblePair) {
            $pairMappingArray[$possiblePair->sourceLang][] = $possiblePair->targetLang;
        }

        $this->cache->set($cacheIdentifier, $pairMappingArray);

        return $pairMappingArray;
    }

    /**
     * @throws Exception
     * @throws SiteNotFoundException
     * @throws \Doctrine\DBAL\Exception
     */
    public function syncGlossaries(int $uid): void
    {
        $glossaries = $this->glossaryRepository->getGlossaryInformationForSync($uid);
        if (empty($glossaries)) {
            throw new FailedToCreateGlossaryException(
                'Glossary can not created, the TYPO3 information are invalide.',
                1714987594661
            );
        }

        foreach ($glossaries as $glossaryInformation) {
            if ($glossaryInformation['glossary_id'] !== '') {
                $this->deleteGlossary($glossaryInformation['glossary_id']);
            }

            try {
                $glossary = $this->createGlossary(
                    $glossaryInformation['glossary_name'],
                    $glossaryInformation['entries'],
                    $glossaryInformation['source_lang'],
                    $glossaryInformation['target_lang']
                );
            } catch (GlossaryEntriesNotExistException $exception) {
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
