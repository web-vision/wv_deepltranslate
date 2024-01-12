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
use DeepL\Usage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @internal No public usage
 */
final class Client extends AbstractClient
{
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
     * @return TextResult|TextResult[]|null
     */
    public function translate(
        string $content,
        ?string $sourceLang,
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

        try {
            return $this->translator->translateText(
                $content,
                $sourceLang,
                $targetLang,
                $options
            );
        } catch (DeepLException $exception) {
            $this->logger->error(sprintf(
                '%s (%d)',
                $exception->getMessage(),
                $exception->getCode()
            ));
        }

        return null;
    }

    /**
     * @return Language[]
     */
    public function getSupportedLanguageByType(string $type = 'target'): array
    {
        try {
            return ($type === 'target')
                ? $this->translator->getTargetLanguages()
                : $this->translator->getSourceLanguages();
        } catch (DeepLException $exception) {
            $this->logger->error(sprintf(
                '%s (%d)',
                $exception->getMessage(),
                $exception->getCode()
            ));
        }

        return [];
    }

    /**
     * @return GlossaryLanguagePair[]
     */
    public function getGlossaryLanguagePairs(): array
    {
        try {
            return $this->translator->getGlossaryLanguages();
        } catch (DeepLException $exception) {
            $this->logger->error(sprintf(
                '%s (%d)',
                $exception->getMessage(),
                $exception->getCode()
            ));
        }

        return [];
    }

    /**
     * @return GlossaryInfo[]
     */
    public function getAllGlossaries(): array
    {
        try {
            return $this->translator->listGlossaries();
        } catch (DeepLException $exception) {
            $this->logger->error(sprintf(
                '%s (%d)',
                $exception->getMessage(),
                $exception->getCode()
            ));
        }

        return [];
    }

    public function getGlossary(string $glossaryId): ?GlossaryInfo
    {
        try {
            return $this->translator->getGlossary($glossaryId);
        } catch (DeepLException $exception) {
            $this->logger->error(sprintf(
                '%s (%d)',
                $exception->getMessage(),
                $exception->getCode()
            ));
        }

        return null;
    }

    /**
     * @param array<int, array{source: string, target: string}> $entries
     */
    public function createGlossary(
        string $glossaryName,
        string $sourceLang,
        string $targetLang,
        array $entries
    ): GlossaryInfo {
        $prepareEntriesForGlossary = [];
        foreach ($entries as $entry) {
            /*
             * as the version without trimming in TCA is already published,
             * we trim a second time here
             * to avoid errors in DeepL client
             */
            $source = trim($entry['source']);
            $target = trim($entry['target']);
            if (empty($source) || empty($target)) {
                continue;
            }
            $prepareEntriesForGlossary[$source] = $target;
        }
        try {
            return $this->translator->createGlossary(
                $glossaryName,
                $sourceLang,
                $targetLang,
                GlossaryEntries::fromEntries($prepareEntriesForGlossary)
            );
        } catch (DeepLException $e) {
            return new GlossaryInfo(
                '',
                '',
                false,
                '',
                '',
                new \DateTime(),
                0
            );
        }
    }

    public function deleteGlossary(string $glossaryId): void
    {
        try {
            $this->translator->deleteGlossary($glossaryId);
        } catch (DeepLException $exception) {
            $this->logger->error(sprintf(
                '%s (%d)',
                $exception->getMessage(),
                $exception->getCode()
            ));
        }
    }

    public function getGlossaryEntries(string $glossaryId): ?GlossaryEntries
    {
        try {
            return $this->translator->getGlossaryEntries($glossaryId);
        } catch (DeepLException $exception) {
            $this->logger->error(sprintf(
                '%s (%d)',
                $exception->getMessage(),
                $exception->getCode()
            ));
        }

        return null;
    }

    /**
     * @throws DeepLException
     */
    public function getUsage(): Usage
    {
        return $this->translator->getUsage();
    }
}
