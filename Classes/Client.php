<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate;

use DeepL\DeepLException;
use DeepL\GlossaryEntries;
use DeepL\GlossaryInfo;
use DeepL\GlossaryLanguagePair;
use DeepL\Language;
use DeepL\TextResult;
use DeepL\TranslateTextOptions;
use DeepL\Translator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class Client
{
    /**
     * @var Configuration
     */
    private $configuration;

    private Translator $translator;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(string $apiKey = '', array $options = [])
    {
        $this->configuration = GeneralUtility::makeInstance(Configuration::class);
        $this->translator = GeneralUtility::makeInstance(
            Translator::class,
            $apiKey ?: $this->configuration->getApiKey(),
            $options
        );
    }

    /**
     * @throws DeepLException
     * @return TextResult|TextResult[]
     */
    public function translate(
        string $content,
        string $sourceLang,
        string $targetLang,
        string $glossary = ''
    ) {
        $options = [
            TranslateTextOptions::FORMALITY => $this->configuration->getFormality(),
            TranslateTextOptions::TAG_HANDLING => 'xml',
        ];

        if (!empty($glossary)) {
            $options[TranslateTextOptions::GLOSSARY] = $glossary;
        }

        return $this->translator->translateText(
            $content,
            $sourceLang,
            $targetLang,
            $options
        );
    }

    /**
     * @throws DeepLException
     * @return Language[]
     */
    public function getSupportedLanguageByType(string $type = 'target'): array
    {
        return ($type === 'target')
            ? $this->translator->getTargetLanguages()
            : $this->translator->getSourceLanguages();
    }

    /**
     * @throws DeepLException
     * @return GlossaryLanguagePair[]
     */
    public function getGlossaryLanguagePairs(): array
    {
        return $this->translator->getGlossaryLanguages();
    }

    /**
     * @throws DeepLException
     * @return GlossaryInfo[]
     */
    public function getAllGlossaries(): array
    {
        return $this->translator->listGlossaries();
    }

    /**
     * @throws DeepLException
     */
    public function getGlossary(string $glossaryId): GlossaryInfo
    {
        return $this->translator->getGlossary($glossaryId);
    }

    /**
     * @param array<int, array{source: string, target: string}> $entries
     * @throws DeepLException
     */
    public function createGlossary(
        string $glossaryName,
        string $sourceLang,
        string $targetLang,
        array $entries
    ): GlossaryInfo {
        $prepareEntriesForGlossary = [];
        foreach ($entries as $entry) {
            $prepareEntriesForGlossary[$entry['source']] = $entry['target'];
        }
        return $this->translator->createGlossary(
            $glossaryName,
            $sourceLang,
            $targetLang,
            GlossaryEntries::fromEntries($prepareEntriesForGlossary)
        );
    }

    /**
     * @throws DeepLException
     */
    public function deleteGlossary(string $glossaryId): void
    {
        $this->translator->deleteGlossary($glossaryId);
    }

    /**
     * @throws DeepLException
     */
    public function getGlossaryEntries(string $glossaryId): GlossaryEntries
    {
        return $this->translator->getGlossaryEntries($glossaryId);
    }
}
