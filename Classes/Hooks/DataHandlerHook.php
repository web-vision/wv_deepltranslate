<?php

declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Hooks;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use WebVision\WvDeepltranslate\Domain\Model\GlossariesSync;
use WebVision\WvDeepltranslate\Domain\Repository\GlossariesRepository;
use WebVision\WvDeepltranslate\Domain\Repository\GlossariesSyncRepository;
use WebVision\WvDeepltranslate\Domain\Repository\LanguageRepository;
use WebVision\WvDeepltranslate\Service\DeeplService;

class DataHandlerHook
{
    protected int $currentPageId = 1;

    private PersistenceManager $persistenceManager;

    protected DeeplService $deeplService;

    protected GlossariesRepository $glossariesRepository;

    protected GlossariesSyncRepository $glossariesSyncRepository;

    protected LanguageRepository $languageRepository;

    public function __construct(
        ?LanguageRepository $languageRepository = null,
        ?GlossariesSyncRepository $glossariesSyncRepository = null,
        ?GlossariesRepository $glossariesRepository = null,
        ?DeeplService $deeplService = null
    ) {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->persistenceManager = $objectManager->get(PersistenceManager::class);
        $this->languageRepository = $languageRepository ?? $objectManager->get(LanguageRepository::class);
        $this->glossariesSyncRepository = $glossariesSyncRepository ?? $objectManager->get(GlossariesSyncRepository::class);
        $this->glossariesRepository = $glossariesRepository ?? $objectManager->get(GlossariesRepository::class);
        $this->deeplService = $deeplService ?? $objectManager->get(DeeplService::class);
    }

    public function processTranslateTo_copyAction(string &$content, array $languageRecord, DataHandler $dataHandler): void
    {
        $cmdmap = $dataHandler->cmdmap;
        foreach ($cmdmap as $key => $array) {
            $tablename = $key;
            foreach ($array as $innerkey => $innervalue) {
                $currectRecordId = $innerkey;
                break;
            }
            break;
        }

        if ($tablename !== 'tx_wvdeepltranslate_domain_model_glossaries') {
            return;
        }
        $this->prepareLangagues('postTranslate', $tablename, $currectRecordId);
    }

    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        string $id,
        array $fieldArray,
        DataHandler $dataHandler
    ): void {
        if ($table !== 'tx_wvdeepltranslate_domain_model_glossaries') {
            return;
        }
        // Only save if its the translated record and not the original term
        if ($status !== 'update') {
            return;
        }

        if (!isset($fieldArray['l10n_parent']) || $fieldArray['l10n_parent'] === 0) {
            return;
        }

        $cmdmap = $dataHandler->cmdmap;
        foreach ($cmdmap as $key => $array) {
            $tablename = $key;
            foreach ($array as $innerkey => $innervalue) {
                $currectRecordId = $innerkey;
                break;
            }
            break;
        }

        if (!MathUtility::canBeInterpretedAsInteger($id)) {
            $id = $dataHandler->substNEWwithIDs[$id];
        }

        $this->prepareLangagues('postDatabase', $table, $id);
    }

    protected function prepareLangagues($action, $tablename, $currectRecordId)
    {
        $glossaryNamePrefix = 'DeepL';

        if (isset($tablename) && isset($currectRecordId)) {
            $currentRecord = BackendUtility::getRecord($tablename, (int)$currectRecordId);
            $this->currentPageId = $currentRecord['pid'];

            try {
                $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
                $site = $siteFinder->getSiteByPageId($currentRecord['pid']);
                $language = $site->getDefaultLanguage();
                $defaultLangIso = $language->getTwoLetterIsoCode();
                $siteLanguages = $site->getLanguages();
            } catch (SiteNotFoundException $exception) {
                // Ignore, use defaults
            }
        }

        if (!empty($siteLanguages)) {
            foreach ($siteLanguages as $language) {
                $langUid = $language->getLanguageId();
                $langIsoCode = $language->getTwoLetterIsoCode();

                // Prepare inputs for DeepL API
                $sourceLang = $defaultLangIso;
                $targetLang = $langIsoCode;

                if ($sourceLang === $targetLang) {
                    continue;
                }

                $entries = $this->glossariesRepository->processGlossariesEntries($langUid);
                $glossaryName = $glossaryNamePrefix . '-' . strtoupper($sourceLang) . '-' . strtoupper($targetLang);

                if (!empty($entries)) {
                    $this->prepareGlossarEntries($glossaryName, $entries, $sourceLang, $targetLang);
                }
            }
        } else {
            $systemLanguages = $this->languageRepository->findAll();

            foreach ($systemLanguages as $language) {
                $langUid = (int)$language->getUid();
                $langIsoCode = $language->getLanguageIsoCode();

                // Prepare inputs for DeepL API
                $sourceLang = $systemLanguages[0]->getLanguageIsoCode();
                $targetLang = $langIsoCode;

                if ($sourceLang === $targetLang) {
                    continue;
                }

                $entries = $this->glossariesRepository->processGlossariesEntries($langUid);
                $glossaryName = $glossaryNamePrefix . '-' . strtoupper($sourceLang) . '-' . strtoupper($targetLang);
            }
            if (!empty($entries)) {
                $this->prepareGlossarEntries($glossaryName, $entries, $sourceLang, $targetLang);
            }
        }
    }

    protected function prepareGlossarEntries($glossaryName, $entries, $sourceLang, $targetLang)
    {
        $glossary = $this->deeplService->createGlossary(
            $glossaryName,
            $entries,
            $sourceLang,
            $targetLang
        );

        $glossaryId = $glossary['glossary_id'];

        if (!empty($glossaryId)) {
            $newGlossarysync = GeneralUtility::makeInstance(GlossariesSync::class);
            $newGlossarysync->setPid($this->currentPageId);
            $newGlossarysync->setGlossaryId($glossaryId);
            $newGlossarysync->setSourceLang($sourceLang);
            $newGlossarysync->setTargetLang($targetLang);
            $newGlossarysync->setEntries(json_encode($entries, JSON_UNESCAPED_UNICODE));

            $this->glossariesSyncRepository->add($newGlossarysync);
            $this->persistenceManager->persistAll();
        }
    }
}
