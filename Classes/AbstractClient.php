<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate;

use DeepL\Translator;
use Psr\Log\LoggerInterface;

/**
 * @internal No public usage
 */
abstract class AbstractClient implements ClientInterface
{
    protected Configuration $configuration;

    protected Translator $translator;

    protected LoggerInterface $logger;

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
