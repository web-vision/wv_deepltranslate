<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core;

use DeepL\GlossaryEntries;
use DeepL\GlossaryInfo;
use DeepL\GlossaryLanguagePair;
use DeepL\Language;
use DeepL\TextResult;
use DeepL\Usage;
use Psr\Log\LoggerAwareInterface;
use WebVision\Deepltranslate\Core\Exception\ApiKeyNotSetException;

/**
 * Interface for custom client implementation and which methods are expected.
 *
 * @internal use only for testing, not part of public extension API.
 */
interface ClientInterface extends LoggerAwareInterface
{
    /**
     * Dispatches an translation request towards the api.
     *
     * @return TextResult|TextResult[]|null
     *
     * @throws ApiKeyNotSetException
     */
    public function translate(
        string $content,
        ?string $sourceLang,
        string $targetLang,
        string $glossary = '',
        string $formality = ''
    );

    /**
     * @return Language[]
     *
     * @throws ApiKeyNotSetException
     */
    public function getSupportedLanguageByType(string $type = 'target'): array;

    /**
     * @return GlossaryLanguagePair[]
     *
     * @throws ApiKeyNotSetException
     */
    public function getGlossaryLanguagePairs(): array;

    /**
     * @return GlossaryInfo[]
     *
     * @throws ApiKeyNotSetException
     */
    public function getAllGlossaries(): array;

    /**
     * @throws ApiKeyNotSetException
     */
    public function getGlossary(string $glossaryId): ?GlossaryInfo;

    /**
     * @param array<int, array{source: string, target: string}> $entries
     *
     * @throws ApiKeyNotSetException
     */
    public function createGlossary(
        string $glossaryName,
        string $sourceLang,
        string $targetLang,
        array $entries
    ): GlossaryInfo;

    /**
     * @throws ApiKeyNotSetException
     */
    public function deleteGlossary(string $glossaryId): void;

    /**
     * @throws ApiKeyNotSetException
     */
    public function getGlossaryEntries(string $glossaryId): ?GlossaryEntries;

    /**
     * @throws ApiKeyNotSetException
     */
    public function getUsage(): ?Usage;
}
