<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Functional;

use DeepL\TranslatorOptions;
use phpmock\phpunit\PHPMock;
use Ramsey\Uuid\Uuid;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use WebVision\WvDeepltranslate\Client;

abstract class DeepLTestCase extends FunctionalTestCase
{
    use PHPMock;

    /**
     * @var string|false
     */
    protected $authKey;

    /**
     * @var string|false
     */
    protected $serverUrl;

    /**
     * @var string|false
     */
    protected $proxyUrl;
    protected bool $isMockServer;
    protected bool $isMockProxyServer;

    protected ?string $sessionNoResponse;
    protected ?string $session429Count;
    protected ?string $sessionInitCharacterLimit;
    protected ?string $sessionInitDocumentLimit;
    protected ?string $sessionInitTeamDocumentLimit;
    protected ?string $sessionDocFailure;
    protected ?int $sessionDocQueueTime;
    protected ?int $sessionDocTranslateTime;
    protected ?bool $sessionExpectProxy;

    protected const EXAMPLE_TEXT = [
        'bg' => 'протонен лъч',
        'cs' => 'protonový paprsek',
        'da' => 'protonstråle',
        'de' => 'Protonenstrahl',
        'el' => 'δέσμη πρωτονίων',
        'en' => 'proton beam',
        'en-US' => 'proton beam',
        'en-GB' => 'proton beam',
        'es' => 'haz de protones',
        'et' => 'prootonikiirgus',
        'fi' => 'protonisäde',
        'fr' => 'faisceau de protons',
        'hu' => 'protonnyaláb',
        'id' => 'berkas proton',
        'it' => 'fascio di protoni',
        'ja' => '陽子ビーム',
        'ko' => '양성자 빔',
        'lt' => 'protonų spindulys',
        'lv' => 'protonu staru kūlis',
        'nb' => 'protonstråle',
        'nl' => 'protonenbundel',
        'pl' => 'wiązka protonów',
        'pt' => 'feixe de prótons',
        'pt-BR' => 'feixe de prótons',
        'pt-PT' => 'feixe de prótons',
        'ro' => 'fascicul de protoni',
        'ru' => 'протонный луч',
        'sk' => 'protónový lúč',
        'sl' => 'protonski žarek',
        'sv' => 'protonstråle',
        'tr' => 'proton ışını',
        'uk' => 'протонний пучок',
        'zh' => '质子束',
    ];

    protected const EXAMPLE_DOCUMENT_INPUT = DeepLTestCase::EXAMPLE_TEXT['en'];
    protected const EXAMPLE_DOCUMENT_OUTPUT = DeepLTestCase::EXAMPLE_TEXT['de'];
    protected string $EXAMPLE_LARGE_DOCUMENT_INPUT;
    protected string $EXAMPLE_LARGE_DOCUMENT_OUTPUT;

    /**
     * @param array<int|string, mixed> $data
     * @throws \Exception
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        $this->EXAMPLE_LARGE_DOCUMENT_INPUT = str_repeat(DeepLTestCase::EXAMPLE_TEXT['en'] . PHP_EOL, 1000);
        $this->EXAMPLE_LARGE_DOCUMENT_OUTPUT = str_repeat(DeepLTestCase::EXAMPLE_TEXT['de'] . PHP_EOL, 1000);

        $this->serverUrl = getenv('DEEPL_SERVER_URL');
        $this->proxyUrl = getenv('DEEPL_PROXY_URL');
        $this->isMockServer = getenv('DEEPL_MOCK_SERVER_PORT') !== false;
        $this->isMockProxyServer = $this->isMockServer && getenv('DEEPL_MOCK_PROXY_SERVER_PORT') !== false;

        if ($this->isMockServer) {
            $this->authKey = 'mock_server';
            if ($this->serverUrl === false) {
                throw new \Exception('DEEPL_SERVER_URL environment variable must be set if using a mock server');
            }
        } else {
            $this->authKey = getenv('DEEPL_AUTH_KEY');
            if ($this->authKey === false) {
                throw new \Exception('DEEPL_AUTH_KEY environment variable must be set unless using a mock server');
            }
        }

        parent::__construct($name, $data, $dataName);
    }

    protected function needsMockServer(): void
    {
        if (!$this->isMockServer) {
            static::markTestSkipped('Test requires mock server');
        }
    }

    protected function needsMockProxyServer(): void
    {
        if (!$this->isMockProxyServer) {
            static::markTestSkipped('Test requires mock proxy server');
        }
    }

    protected function needsRealServer(): void
    {
        if ($this->isMockServer) {
            static::markTestSkipped('Test requires real server');
        }
    }

    private function makeSessionName(): string
    {
        return $this->getName() . '/' . Uuid::uuid4();
    }

    /**
     * @return array<string, mixed>
     */
    private function sessionHeaders(): array
    {
        $result = [];
        if ($this->sessionNoResponse !== null) {
            $result['mock-server-session-no-response-count'] = (string)($this->sessionNoResponse);
        }
        if ($this->session429Count !== null) {
            $result['mock-server-session-429-count'] = (string)($this->session429Count);
        }
        if ($this->sessionInitCharacterLimit !== null) {
            $result['mock-server-session-init-character-limit'] = (string)($this->sessionInitCharacterLimit);
        }
        if ($this->sessionInitDocumentLimit !== null) {
            $result['mock-server-session-init-document-limit'] = (string)($this->sessionInitDocumentLimit);
        }
        if ($this->sessionInitTeamDocumentLimit !== null) {
            $result['mock-server-session-init-team-document-limit'] = (string)($this->sessionInitTeamDocumentLimit);
        }
        if ($this->sessionDocFailure !== null) {
            $result['mock-server-session-doc-failure'] = (string)($this->sessionDocFailure);
        }
        if ($this->sessionDocQueueTime !== null) {
            $result['mock-server-session-doc-queue-time'] = (string)($this->sessionDocQueueTime * 1000);
        }
        if ($this->sessionDocTranslateTime !== null) {
            $result['mock-server-session-doc-translate-time'] = (string)($this->sessionDocTranslateTime * 1000);
        }
        if ($this->sessionExpectProxy !== null) {
            $result['mock-server-session-expect-proxy'] = $this->sessionExpectProxy ? '1' : '0';
        }

        if (count($result) > 0) {
            $result['mock-server-session'] = $this->makeSessionName();
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function makeClient(array $options = []): Client
    {
        $mergedOptions = array_replace(
            [TranslatorOptions::HEADERS => $this->sessionHeaders()],
            $options
        );

        if ($this->serverUrl !== false) {
            $mergedOptions[TranslatorOptions::SERVER_URL] = $this->serverUrl;
        }

        return new Client($this->authKey ?: '', $mergedOptions);
    }

    public static function readFile(string $filepath): string
    {
        $size = filesize($filepath);
        if ($size == 0) {
            return '';
        }
        $fh = fopen($filepath, 'r');
        $size = filesize($filepath);
        $content = '';
        if ($fh !== false && $size !== false) {
            $content = fread($fh, $size);
            fclose($fh);
        }
        return $content ?: '';
    }

    public static function writeFile(string $filepath, string $content): void
    {
        $fh = fopen($filepath, 'w');
        if ($fh !== false) {
            fwrite($fh, $content);
            fclose($fh);
        }
    }

    /**
     * @return string[]
     */
    public function tempFiles(): array
    {
        $tempDir = sys_get_temp_dir() . '/deepl-php-test-' . Uuid::uuid4() . '/';
        $exampleDocument = $tempDir . 'example_document.txt';
        $exampleLargeDocument = $tempDir . 'example_large_document.txt';
        $outputDocumentPath = $tempDir . 'output_document.txt';

        mkdir($tempDir);
        $this->writeFile($exampleDocument, DeepLTestCase::EXAMPLE_DOCUMENT_INPUT);
        $this->writeFile($exampleLargeDocument, $this->EXAMPLE_LARGE_DOCUMENT_INPUT);

        return [$tempDir, $exampleDocument, $exampleLargeDocument, $outputDocumentPath];
    }

    public function assertExceptionContains(string $needle, callable $function): \Exception
    {
        try {
            $function();
        } catch (\Exception $exception) {
            static::assertStringContainsString($needle, $exception->getMessage());
            return $exception;
        }
        static::fail("Expected exception containing '$needle' but nothing was thrown");
    }

    /**
     * @param class-string $class
     */
    public function assertExceptionClass(string $class, callable $function): \Exception
    {
        try {
            $function();
        } catch (\Exception $exception) {
            static::assertEquals($class, get_class($exception));
            return $exception;
        }
        static::fail("Expected exception of class '$class' but nothing was thrown");
    }

    /**
     * This is necessary due to https://github.com/php-mock/php-mock-phpunit#restrictions
     * In short, as these methods can be called by other tests before UserAgentTest and other
     * tests that use their mocks are executed, we need to call `defineFunctionMock` before
     * calling the unmocked function, or the mock will not work.
     * Otherwise the tests will fail with:
     *     Expectation failed for method name is "delegate" when invoked 1 time(s).
     *     Method was expected to be called 1 times, actually called 0 times.
     */
    public static function setUpBeforeClass(): void
    {
        self::defineFunctionMock(__NAMESPACE__, 'curl_exec');
        self::defineFunctionMock(__NAMESPACE__, 'curl_getinfo');
        self::defineFunctionMock(__NAMESPACE__, 'curl_setopt_array');
    }
}
