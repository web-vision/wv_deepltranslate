<?php

declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Controller;

use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use WebVision\WvDeepltranslate\Domain\Model\GlossariesSync;
use WebVision\WvDeepltranslate\Domain\Repository\GlossariesRepository;
use WebVision\WvDeepltranslate\Domain\Repository\GlossariesSyncRepository;
use WebVision\WvDeepltranslate\Domain\Repository\LanguageRepository;
use WebVision\WvDeepltranslate\Service\DeeplService;

/**
 * ToDo: Handle more lange option as "de" and "en"
 */
class GlossarySyncController
{
    private const NAME_PREFIX = 'DeepL';

    private const DEFAULT_SOURCE_LANG_ISO = 'de';

    private const DEFAULT_TARGET_LANG_ISO = 'en';

    /**
     * @var PersistenceManager
     */
    private $persistenceManager;

    /**
     * @var DeeplService
     */
    private $deeplService;
    /**
     * @var GlossariesRepository
     */
    private $glossariesRepository;

    /**
     * @var GlossariesSyncRepository
     */
    private $glossariesSyncRepository;

    /**
     * @var LanguageRepository
     */
    private $languageRepository;

    public function __construct(
        ?DeeplService $deeplService = null,
        ?GlossariesRepository $glossariesRepository = null,
        ?GlossariesSyncRepository $glossariesSyncRepository = null,
        ?LanguageRepository $languageRepository = null
    ) {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->persistenceManager = $objectManager->get(PersistenceManager::class);
        $this->deeplService = $deeplService ?? $objectManager->get(DeeplService::class);
        $this->glossariesRepository = $glossariesRepository ?? $objectManager->get(GlossariesRepository::class);
        $this->glossariesSyncRepository = $glossariesSyncRepository ?? $objectManager->get(GlossariesSyncRepository::class);
        $this->languageRepository =  $languageRepository ?? $objectManager->get(LanguageRepository::class);
    }

    public function update(): RedirectResponse
    {
        $systemLanguages = $this->languageRepository->findAll();

        if ($systemLanguages->count() === 0) {
            return new RedirectResponse('#');
        }

        // First do some cleanup tasks
        // ToDo - Need to move this a new task
        // $this->doCleanupTasks();

        foreach ($systemLanguages as $lang) {
            $langUid = (int)$lang->getUid();
            // Why this?
            $langIsoCode = $lang->getLanguageIsoCode();

            $entries = $this->glossariesRepository->processGlossariesEntries($langUid);
            $glossaryName = sprintf(
                '%s-%s-%s',
                self::NAME_PREFIX,
                strtoupper(self::DEFAULT_SOURCE_LANG_ISO),
                strtoupper(self::DEFAULT_TARGET_LANG_ISO)
            );

            if (empty($entries)) {
                continue;
            }

            // Create Glossary through API and a DB entry
            $glossary = $this->deeplService->createGlossary(
                $glossaryName,
                $entries,
                self::DEFAULT_SOURCE_LANG_ISO,
                self::DEFAULT_TARGET_LANG_ISO
            );

            $glossaryId = $glossary['glossary_id'];

            if (!empty($glossaryId)) {
                $newGlossarysync = GeneralUtility::makeInstance(GlossariesSync::class);
                $newGlossarysync->setGlossaryId($glossaryId);
                $newGlossarysync->setSourceLang(self::DEFAULT_SOURCE_LANG_ISO);
                $newGlossarysync->setTargetLang(self::DEFAULT_TARGET_LANG_ISO);
                $newGlossarysync->setEntries(json_encode($entries));
                $this->glossariesSyncRepository->add($newGlossarysync);
                $this->persistenceManager->persistAll();
            }
        }

        return new RedirectResponse('#');
    }
}
