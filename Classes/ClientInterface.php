<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate;

use DeepL\GlossaryEntries;
use DeepL\GlossaryInfo;
use DeepL\GlossaryLanguagePair;
use DeepL\Language;
use DeepL\TextResult;
use DeepL\Usage;
use Psr\Log\LoggerAwareInterface;

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
     */
    public function translate(
        string $content,
        ?string $sourceLang,
        string $targetLang,
        string $glossary = ''
    );

    /**
     * @return Language[]
     */
    public function getSupportedLanguageByType(string $type = 'target'): array;

    /**
     * @return GlossaryLanguagePair[]
     */
    public function getGlossaryLanguagePairs(): array;

    /**
     * @return GlossaryInfo[]
     */
    public function getAllGlossaries(): array;

    public function getGlossary(string $glossaryId): ?GlossaryInfo;

    /**
     * @param array<int, array{source: string, target: string}> $entries
     */
    public function createGlossary(
        string $glossaryName,
        string $sourceLang,
        string $targetLang,
        array $entries
    ): GlossaryInfo;

    public function deleteGlossary(string $glossaryId): void;

    public function getGlossaryEntries(string $glossaryId): ?GlossaryEntries;

    public function getUsage(): ?Usage;
}
