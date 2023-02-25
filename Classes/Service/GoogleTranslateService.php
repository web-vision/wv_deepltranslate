<?php

declare(strict_types=1);

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
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @deprecated In a future version, Google Translate will be removed and only deepL will be supported
 */
class GoogleTranslateService
{
    public RequestFactory $requestFactory;

    public string $apiKey;

    public string $apiUrl;

    public function __construct()
    {
        $this->requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
        $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('wv_deepltranslate');
        $this->apiUrl = $extConf['googleapiUrl'];
        $this->apiKey = $extConf['googleapiKey'];
    }
    /**
     * Passes translate request and formats response returned on request
     * @deprecated In a future version, Google Translate will be removed and only deepL will be supported
     */
    public function translate(string $source, string $target, string $text): string
    {
        // Request translation
        $response = $this->request($source, $target, $text);
        // Get translated text from response
        $translation = $this->getTranslation($response);

        return $translation;
    }

    /**
     * make translate request to api
     */
    protected function request(string $source, string $target, string $text): array
    {
        //translate request with api key(non-free mode - recommended)
        if ($this->apiKey != '' && $this->apiUrl != '') {
            $url    = $this->apiUrl . '?key=' . $this->apiKey;
            $fields = [
                'source' => urlencode($source),
                'target' => urlencode($target),
                'q'      => $text,
            ];
        }
        //translate request without apikey(free mode)
        else {
            $url = 'https://translate.google.com/translate_a/single?client=at&dt=t&dt=ld&dt=qca&dt=rm&dt=bd&dj=1&hl=es-ES&ie=UTF-8&oe=UTF-8&inputm=2&otf=2&iid=1dd3b944-fa62-4b55-b330-74909a99969e';

            $fields = [
                'sl' => urlencode($source),
                'tl' => urlencode($target),
                'q'  => $text,
            ];
            $result = [];
            //checks for number of characters
            if (strlen($fields['q']) >= 5000) {
                $result['status']  = 'false';
                $result['message'] = 'Maximum number of characters exceeded: 5000';
                $result = json_encode($result);
                echo $result;
                exit;
            }
        }

        // URL-ify the data for the POST
        $fields_string = '';
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }

        $fields_string = rtrim($fields_string, '&');
        $contentLength = mb_strlen($fields_string, '8bit');
        try {
            $response = $this->requestFactory->request($url, 'POST', [
                'form_params' => $fields,
                'headers'     => [
                    'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
                    'Content-Length' => $contentLength,
                ],
            ]);
        } catch (ClientException $e) {
            $result['status']  = 'false';
            if ($e->getCode() == 404) {
                $result['message'] = 'Google Api url not reachable.Check whether the Api url provided in extension configuration is valid(non freemode).';
            } else {
                $result['message'] = $e->getMessage();
            }
            $result = json_encode($result);
            echo $result;
            exit;
        }

        $result = json_decode($response->getBody()->getContents(), true);
        if (json_last_error() > 0) {
            return [];
        }

        return $result;
    }

    /**
     * Formats the response to get the translated text
     *
     * @param array{data: array[], sentences: string[]} $response
     * @return string
     */
    protected function getTranslation(array $response): string
    {
        $translation = '';
        if ($this->apiKey != '' && $this->apiUrl != '') {
            $translation = $response['data']['translations'][0]['translatedText'];
        } else {
            foreach ($response['sentences'] as $text) {
                $text = self::googleTranslationPostprocess($text);
                $translation .= $text['trans'] ?? '';
            }
        }
        return $translation;
    }

    /**
     * Post processing returned translation
     *
     * @param string $translate
     * @return array{trans: string}
     */
    public function googleTranslationPostprocess($translate): array
    {
        $translate['trans'] = str_replace('</ ', '</', $translate['trans']);
        $translate['trans'] = preg_replace('/(?:&\slt;|&lt;)+/', '<', $translate['trans']);
        $translate['trans'] = preg_replace('/(?:&\sgt;|&gt;)+/', '>', $translate['trans']);
        $translate['trans'] = preg_replace('/(?:&\snbsp;|&nbsp;|&Nbsp;|&\sNbsp;)+/', '', $translate['trans']);
        $translate['trans'] = preg_replace('/(?:href\s="\s|href="\s)+/', 'href="', $translate['trans']);

        return $translate;
    }
}
