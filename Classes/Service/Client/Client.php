<?php

namespace WebVision\WvDeepltranslate\Service\Client;

use TYPO3\CMS\Core\Http\Request;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Utility\DeeplBackendUtility;

/**
 * Class Client implements a DeepL http Client based on PHP-CURL
 * actually ONLY used by GlossaryService
 */
final class Client implements ClientInterface
{
    private const HTTP_CODE_READ = [
        200,
        201,
    ];
    private const API_URL_SCHEMA = 'https';

    /**
     * API BASE URL without authentication query parameter
     * https://api.deepl.com/v2/[resource]
     */
    public const API_URL_BASE_NO_AUTH = '%s://%s/v%s/%s';

    /**
     * DeepL API Version (v2 is default since 2018)
     *
     * @var int
     */
    protected $apiVersion = 2;

    /**
     * DeepL API Auth Key (DeepL Pro access required)
     *
     * @var string
     */
    protected $authKey;

    /**
     * Hostname of the API (in most cases api.deepl.com)
     *
     * @var string
     */
    protected $host;

    /**
     * Maximum number of seconds the query should take
     *
     * @var int|null
     */
    protected $timeout;

    /**
     * URL of the proxy used to connect to DeepL (if needed)
     *
     * @var string|null
     */
    protected $proxy;

    /**
     * Credentials for the proxy used to connect to DeepL (username:password)
     *
     * @var string|null
     */
    protected $proxyCredentials;

    /**
     * DeepL constructor
     *
     * @param string  $authKey
     * @param int $apiVersion
     * @param string  $host
     */
    public function __construct()
    {
        $this->authKey    = DeeplBackendUtility::getApiKey();
        // ugly, but only this way all functions will still keep alive, do better
        // and detect
        $this->host       = parse_url(DeeplBackendUtility::getApiUrl(), PHP_URL_HOST);
    }

    /**
     * Make a request to the given URL
     *
     * @param string $url
     * @param string $body
     * @param string $method
     *
     * @return array|mixed|null
     *
     * @throws DeepLException
     */
    public function request($url, $body = '', $method = 'POST')
    {
        $request = new Request(
            $url,
            $method,
            null,
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => sprintf('DeepL-Auth-Key %s', $this->authKey),
                'User-Agent' => 'TYPO3.WvDeepltranslate/1.0',
            ]
        );

        $options = [
            'body' => $body,
        ];

        // read TYPO3 Proxy settings and adapt
        if (!empty($GLOBALS['TYPO3_CONF_VARS']['HTTP']['proxy'])) {
            $httpProxy = $GLOBALS['TYPO3_CONF_VARS']['HTTP']['proxy'];
            if (is_string($httpProxy)) {
                $options['proxy'] = [
                    'http' => $httpProxy,
                    'https' => $httpProxy,
                ];
            }
            if (is_array($httpProxy)) {
                $options['proxy'] = [
                    'http' => $httpProxy['http'] ?: '',
                    'https' => $httpProxy['https']
                        ?: $httpProxy['http'] ?: '',
                ];
            }
        }

        $response = (new \GuzzleHttp\Client())->send($request, $options);

        if (in_array($response->getStatusCode(), self::HTTP_CODE_READ)) {
            return json_decode($response->getBody()->getContents(), true);
        }

        return [];
    }

    /**
     * Set a proxy to use for querying the DeepL API if needed
     *
     * @param string $proxy Proxy URL (e.g 'http://proxy-domain.com:3128')
     */
    public function setProxy($proxy)
    {
        $this->proxy = $proxy;
    }

    /**
     * Set the proxy credentials
     *
     * @param string $proxyCredentials proxy credentials (using 'username:password' format)
     */
    public function setProxyCredentials($proxyCredentials)
    {
        $this->proxyCredentials = $proxyCredentials;
    }

    /**
     * Set a timeout for queries to the DeepL API
     *
     * @param int $timeout Timeout in seconds
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * Creates the Base-Url which all the API-resources have in common.
     *
     * @param string $resource
     * @return string
     */
    public function buildBaseUrl(string $resource = 'translate'): string
    {
        return sprintf(
            self::API_URL_BASE_NO_AUTH,
            self::API_URL_SCHEMA,
            $this->host,
            $this->apiVersion,
            $resource
        );
    }

    /**
     * @param array $paramsArray
     * @return string
     */
    public function buildQuery($paramsArray): string
    {
        if (isset($paramsArray['text']) && true === is_array($paramsArray['text'])) {
            $text = $paramsArray['text'];
            unset($paramsArray['text']);
            $textString = '';
            foreach ($text as $textElement) {
                $textString .= '&text=' . rawurlencode($textElement);
            }
        }

        foreach ($paramsArray as $key => $value) {
            if (true === is_array($value)) {
                $paramsArray[$key] = implode(',', $value);
            }
        }

        $body = http_build_query($paramsArray, '', '&');

        if (isset($textString)) {
            $body = $textString . '&' . $body;
        }

        return $body;
    }

    /**
     * Handles the different kind of response returned from API, array, string or null
     *
     * @param string|bool $response
     * @param int $httpCode
     * @return array|mixed|null
     * @throws DeepLException
     */
    private function handleResponse($response, $httpCode)
    {
        $responseArray = json_decode($response, true);
        if (($httpCode === 200 || $httpCode === 204) && is_null($responseArray)) {
            return empty($response) ? null : $response;
        }

        if ($httpCode !== 200 && is_array($responseArray) && array_key_exists('message', $responseArray)) {
            if (str_contains($responseArray['message'], 'Unsupported')) {
                // FlashMessage($message, $title, $severity = self::OK, $storeInSession)
                $message = GeneralUtility::makeInstance(
                    FlashMessage::class,
                    $responseArray['message'],
                    'DeepL Api',
                    FlashMessage::ERROR,
                    true
                );
                $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
                $messageQueue->addMessage($message);
            } else {
                throw new DeepLException($responseArray['message'], $httpCode);
            }
        }

        if (!is_array($responseArray)) {
            throw new DeepLException('The Response seems to not be valid JSON.', $httpCode);
        }

        return $responseArray;
    }
}
