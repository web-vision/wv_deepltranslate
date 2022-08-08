<?php
declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Controller;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use WebVision\WvDeepltranslate\Domain\Model\Glossariessync;
use WebVision\WvDeepltranslate\Domain\Repository\GlossariesRepository;
use WebVision\WvDeepltranslate\Domain\Repository\GlossariessyncRepository;
use WebVision\WvDeepltranslate\Domain\Repository\LanguageRepository;
use WebVision\WvDeepltranslate\Service\DeeplGlossaryService;

class GlossarySyncController
{
    public function update()
    {
        // $glossaryPageUid = $request->getQueryParams()['uid'];

        //create redirect url to edit view of book dataset
        // $backendUriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        // $uriParameters = ['edit' => ['tx_wvdeepltranslate_domain_model_glossariessync' => [12 => 'edit']]];
        // $editLink = $backendUriBuilder->buildUriFromRoute('record_edit',
        //     $uriParameters);

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->persistenceManager = $objectManager->get(PersistenceManager::class);
        $this->deeplGlossaryService = $objectManager->get(DeeplGlossaryService::class);
        $this->glossariesRepository = $objectManager->get(GlossariesRepository::class);
        $this->glossariessyncRepository = $objectManager->get(GlossariessyncRepository::class);
        $this->languageRepository = $objectManager->get(LanguageRepository::class);

        $systemLanguages = $this->languageRepository->findAll();

        if ($systemLanguages->count() > 0) {
            // First do some cleanup tasks
            // @TODO - Need to move this a new task
            // $this->doCleanupTasks();

            // Process for a new sync
            $glossaryNamePrefix = 'DeepL';
            $defaultLangIso = 'de';
            $sourceLang = $defaultLangIso;

            foreach ($systemLanguages as $lang) {
                $langUid = (int)$lang->getUid();
                $langIsoCode = $lang->getLanguageIsoCode();

                // Prepare inputs for DeepL API
                $targetLang = 'en';

                $entries = $this->glossariesRepository->processGlossariesEntries($langUid);
                $glossaryName = $glossaryNamePrefix . '-' . strtoupper($sourceLang) . '-' . strtoupper($targetLang);
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
                        $newGlossarysync->setEntries(json_encode($entries));
                        $this->glossariessyncRepository->add($newGlossarysync);
                        $this->persistenceManager->persistAll();
                    }
                }
            }
        }
        return new RedirectResponse('#');
    }
}
