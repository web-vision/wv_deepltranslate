<?php
declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Ricky Mathew <ricky@web-vision.de>, web-vision GmbH
 *      Anu Bhuvanendran Nair <anu@web-vision.de>, web-vision GmbH
 *
 *  You may not remove or change the name of the author above. See:
 *  http://www.gnu.org/licenses/gpl-faq.html#IWantCredit
 *
 *  This script is part of the Typo3 project. The Typo3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use GuzzleHttp\Exception\ClientException;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use WebVision\WvDeepltranslate\Domain\Repository\GlossariesSyncRepository;
use WebVision\WvDeepltranslate\Domain\Repository\SettingsRepository;
use WebVision\WvDeepltranslate\Client;

class DeeplService
{
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

    protected GlossariesSyncRepository $glossariesSyncRepository;

    private FrontendInterface $cache;

    private Client $client;

    public function __construct(?FrontendInterface $cache = null, ?Client $client = null)
    {
        $this->cache = $cache ?? GeneralUtility::makeInstance(CacheManager::class)->getCache('wvdeepltranslate');
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->deeplSettingsRepository = $objectManager->get(SettingsRepository::class);
        $this->glossariesSyncRepository = $objectManager->get(GlossariesSyncRepository::class);
        $this->client = $client ?? GeneralUtility::makeInstance(Client::class);;

        $this->loadSupportedLanguages();
        $this->apiSupportedLanguages['target'] = $this->deeplSettingsRepository->getSupportedLanguages($this->apiSupportedLanguages['target']);
    }

    /**
     * Deepl Api Call for retrieving translation.
     * @return object json-object
     */
    public function translateRequest($content, $targetLanguage, $sourceLanguage): object
    {
        // Implementation of glossary into translation
        $glossaryId = $this->glossariesSyncRepository->getGlossaryIdByLanguages($sourceLanguage, $targetLanguage);

        try {
            $response = $this->client->translate($content, $sourceLanguage, $targetLanguage, $glossaryId);
        } catch (ClientException $e) {
            $result            = [];
            $result['status']  = 'false';
            $result['message'] = $e->getMessage();
            $result            = json_encode($result);
            echo $result;
            exit;
        }

        return json_decode($response->getBody()->getContents());
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
            if ($supportedLanguage['supports_formality'] === true) {
                $this->formalitySupportedLanguages[] = $supportedLanguage['language'];
            }
        }
    }

    private function loadSupportedLanguagesFromAPI(string $type = 'target'): array
    {
        try {
            $response = $this->client->getSupportedTargetLanguage();
        } catch (ClientException $e) {
            $result            = [];
            $result['status']  = 'false';
            $result['message'] = $e->getMessage();
            $result            = json_encode($result);
            echo $result;
            exit;
        }

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * @return array<string, mixed>
     */
    public function listGlossaryLanguagePairs(): array
    {
        $response = $this->client->getGlossaryLanguagePairs();

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @return array
     */
    public function listGlossaries(): array
    {
        $response = $this->client->getAllGlossaries();

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @return array|null
     */
    public function deleteGlossary(string $glossaryId): bool
    {
        // ToDo: Add success full return
        $response = $this->client->deleteGlossary($glossaryId);

        return true;
    }

    /**
     * @return array|null
     */
    public function glossaryInformation(string $glossaryId): array
    {
        $response = $this->client->getGlossary($glossaryId);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Fetch glossary entries and format them as associative array [source => target]
     *
     * @return array
     */
    public function glossaryEntries(string $glossaryId): array
    {
        $response = $this->client->getGlossaryEntries($glossaryId);

        $jsons = json_decode($response->getBody()->getContents(), true);

        $entries = [];

        $allEntries = explode("\n", $jsons);
        foreach ($allEntries as $entry) {
            $sourceAndTarget = preg_split('/\s+/', rtrim($entry));
            if (isset($sourceAndTarget[0], $sourceAndTarget[1])) {
                $entries[$sourceAndTarget[0]] = $sourceAndTarget[1];
            }
        }

        return $entries;
    }
}
