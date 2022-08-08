<?php
declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Hooks;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use WebVision\WvDeepltranslate\Domain\Model\Glossariessync;
use WebVision\WvDeepltranslate\Domain\Repository\GlossariesRepository;
use WebVision\WvDeepltranslate\Domain\Repository\GlossariessyncRepository;
use WebVision\WvDeepltranslate\Domain\Repository\LanguageRepository;
use WebVision\WvDeepltranslate\Service\DeeplGlossaryService;

class DataHandlerHook implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private NotificationRepository $notificationRepository;

    private EventDispatcherInterface $eventDispatcher;

    private PersistenceManager $persistenceManager;

    protected DeeplGlossaryService $deeplGlossaryService;

    protected GlossariesRepository $glossariesRepository;

    protected GlossariessyncRepository $glossariessyncRepository;

    protected LanguageRepository $languageRepository;

    public function injectLanguageRepository(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    public function injectGlossariessyncRepository(GlossariessyncRepository $glossariessyncRepository)
    {
        $this->glossariessyncRepository = $glossariessyncRepository;
    }

    public function injectGlossariesRepository(GlossariesRepository $glossariesRepository)
    {
        $this->glossariesRepository = $glossariesRepository;
    }

    public function injectDeeplGlossaryService(DeeplGlossaryService $deeplGlossaryService)
    {
        $this->deeplGlossaryService = $deeplGlossaryService;
    }

    public function injectPersistenceManager(PersistenceManager $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
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
        $cmdmap = $dataHandler->cmdmap;
        foreach ($cmdmap as $key => $array) {
            $tablename = $key;
            foreach ($array as $innerkey => $innervalue) {
                $currectRecordId = $innerkey;
                break;
            }
            break;
        }
        if ($table !== 'tx_wvdeepltranslate_domain_model_glossaries') {
            return;
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

                $entries = $this->glossariesRepository->processGlossariesEntries($langUid);
                $glossaryName = $glossaryNamePrefix . '-' . strtoupper($sourceLang) . '-' . strtoupper($targetLang);

                if (!empty($entries)) {
                    $this->prepareGlossarEntries($glossaryName, $entries, $sourceLang, $targetLang);
                }
            }
        }
    }

    protected function prepareGlossarEntries($glossaryName, $entries, $sourceLang, $targetLang)
    {
        // Create Glossary through API and a DB entry
        $glossary = $this->deeplGlossaryService->createGlossary(
            $glossaryName,
            $entries,
            $sourceLang,
            $targetLang
        );

        $glossaryId = $glossary['glossary_id'];

        if (!empty($glossaryId)) {
            $newGlossarysync = GeneralUtility::makeInstance(Glossariessync::class);
            $newGlossarysync->setGlossaryId($glossaryId);
            $newGlossarysync->setSourceLang($sourceLang);
            $newGlossarysync->setTargetLang($targetLang);
            $newGlossarysync->setEntries(json_encode($entries, JSON_UNESCAPED_UNICODE));
            $this->glossariessyncRepository->add($newGlossarysync);
            $this->persistenceManager->persistAll();
        }
    }

    protected function getRequest()
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}
