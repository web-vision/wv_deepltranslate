<?php
declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Hooks;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use Psr\EventDispatcher\EventDispatcherInterface;
use WebVision\WvDeepltranslate\Domain\Model\Glossariessync;
use WebVision\WvDeepltranslate\Service\DeeplGlossaryService;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use WebVision\WvDeepltranslate\Domain\Repository\LanguageRepository;
use TYPO3\CMS\Backend\Configuration\TranslationConfigurationProvider;
use WebVision\WvDeepltranslate\Domain\Repository\GlossariesRepository;
use WebVision\WvDeepltranslate\Domain\Repository\GlossariessyncRepository;

class DataHandlerHook implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var NotificationRepository
     */
    private $notificationRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
    }

    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        string $id,
        array $fieldArray,
        DataHandler $dataHandler
    ): void {
        if ($status !== 'new' && $status !== 'update') {
            return;
        }
        if ($table !== 'tx_wvdeepltranslate_domain_model_glossaries') {
            return;
        }

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->persistenceManager = $objectManager->get(PersistenceManager::class);
        $this->deeplGlossaryService = $objectManager->get(DeeplGlossaryService::class);
        $this->glossariesRepository = $objectManager->get(GlossariesRepository::class);
        $this->glossariessyncRepository = $objectManager->get(GlossariessyncRepository::class);
        $this->languageRepository = $objectManager->get(LanguageRepository::class);

        $systemLanguages = $this->languageRepository->findAll();
        $defaultLanguage = $this->languageRepository->findByUid(0);
        $defaultLangIso = $defaultLanguage->getLanguageIsoCode();

        if ($systemLanguages->count() > 0) {

            $glossaryNamePrefix = 'DeepL';

            foreach($systemLanguages as $lang) {
                $langUid = (int) $lang->getUid();
                $langIsoCode = $lang->getLanguageIsoCode();

                // Prepare inputs for DeepL API
                $sourceLang = $defaultLangIso;
                $targetLang = $langIsoCode;
                $entries = $this->glossariesRepository->processGlossariesEntries($langUid);
                $glossaryName = $glossaryNamePrefix.'-'.strtoupper($sourceLang).'-'.strtoupper($targetLang);

                if (!empty($entries)) {
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
            }
        }
    }

    /**
    * @return ServerRequestInterface
     */
    protected function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}
