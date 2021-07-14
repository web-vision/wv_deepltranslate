<?php
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
use WebVision\WvDeepltranslate\Domain\Repository\DeeplSettingsRepository;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

class DeeplService
{
    /**
     * @var type
     */
    protected $curlHandle;

    /**
     * @var string
     */
    public $apiKey;

    /**
     * @var string
     */
    public $apiUrl;

    /**
     * @var string
     */
    public $deeplFormality;

    /**
     * Default supported languages
     * @see https://www.deepl.com/de/docs-api/translating-text/#request
     * @var array
     */
    public $apiSupportedLanguages = ['BG', 'CS', 'DA', 'DE', 'EL', 'EN', 'ES', 'ET', 'FI', 'FR', 'HU', 'IT', 'JA', 'LT', 'LV', 'NL', 'PL', 'PT', 'RO', 'RU', 'SK', 'SL', 'SV', 'ZH'];

    /**
     * Formality supported languages
     * @var array
     */
    public $formalitySupportedLanguages = ['DE', 'FR', 'IT', 'ES', 'NL', 'PL', 'PT-PT', 'PT-BR', 'RU'];

    /**
     * @var RequestFactory
     */
    public $requestFactory;

    /**
     * @var \WebVision\WvDeepltranslate\Domain\Repository\DeeplSettingsRepository
     */
    protected $deeplSettingsRepository;

    /**
     * Description
     * @return type
     */
    public function __construct()
    {
        $extConf                       = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('wv_deepltranslate');
        
        $this->deeplSettingsRepository = GeneralUtility::makeInstance(DeeplSettingsRepository::class);
        $this->requestFactory          = GeneralUtility::makeInstance(RequestFactory::class);

        $this->apiUrl                  = $extConf['apiUrl'];
        $this->apiKey                  = $extConf['apiKey'];
        $this->deeplFormality          = $extConf['deeplFormality'];
        $this->apiSupportedLanguages   = $this->deeplSettingsRepository->getSupportedLanguages($this->apiSupportedLanguages);
    }

    /**
     * Deepl Api Call for retrieving translation.
     * @return type
     */
    public function translateRequest($content, $targetLanguage, $sourceLanguage)
    {
        $postFields = [
            'auth_key'     => $this->apiKey,
            'text'         => $content,
            'source_lang'  => urlencode($sourceLanguage),
            'target_lang'  => urlencode($targetLanguage),
            'tag_handling' => urlencode('xml'),
        ];
        if (!empty($this->deeplFormality) && in_array(strtoupper($targetLanguage), $this->formalitySupportedLanguages, true)) {
            $postFields['formality'] = $this->deeplFormality;
        }
        //url-ify the data to get content length
        foreach ($postFields as $key => $value) {
            $postFieldString .= $key . '=' . $value . '&';
        }
        rtrim($postFieldString, '&');
        $contentLength = mb_strlen($postFieldString, '8bit');

        try {
            $response = $this->requestFactory->request($this->apiUrl, 'POST', [
                'form_params' => $postFields,
                'headers'     => ['Content-Type: application/x-www-form-urlencoded', 'Content-Length:' . $contentLength],
            ]);
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
}
