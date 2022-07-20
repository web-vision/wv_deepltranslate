<?php
declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2022 Kallol Chakraborty <kallol@web-vision.de>, web-vision GmbH
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
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use WebVision\WvDeepltranslate\Service\Client\Client;
class DeeplGlossaryService
{
    /**
     * URL Suffix: glossaries
     */
    const API_URL_SUFFIX_GLOSSARIES = 'glossaries';

    /**
     * URL Suffix: glossary-language-pairs
     */
    const API_URL_SUFFIX_GLOSSARIES_LANG_PAIRS = 'glossary-language-pairs';

    /**
     * API Version:
     */
    const API_VERSION = '2';

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

    public RequestFactory $requestFactory;

    public function __construct()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->requestFactory = GeneralUtility::makeInstance(RequestFactory::class);

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
     * @param string $entriesFormat
     *
     * @return array|null
     *
     * @throws DeepLException
     */
    public function createGlossary(
        string $name,
        array $entries,
        string $sourceLang = 'de',
        string $targetLang = 'en',
        string $entriesFormat = 'tsv'
    ) {
        $formattedEntries = "";
        foreach ($entries as $source => $target) {
            $formattedEntries .= sprintf("%s\t%s\n", $source, $target);
        }

        $paramsArray = [
            'name' => $name,
            'source_lang'    => $sourceLang,
            'target_lang'    => $targetLang,
            'entries'        => $formattedEntries,
            'entries_format' => $entriesFormat
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

        $this->client->request($url, '', 'DELETE');
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
            $allEntries = preg_split('/\n/', $response);
            foreach ($allEntries as $entry) {
                $sourceAndTarget = preg_split('/\s+/', rtrim($entry));
                if (isset($sourceAndTarget[0], $sourceAndTarget[1])) {
                    $entries[$sourceAndTarget[0]] = $sourceAndTarget[1];
                }
            }
        }

        return $entries;
    }
}
