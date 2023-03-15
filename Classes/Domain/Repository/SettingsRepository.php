<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use WebVision\WvDeepltranslate\Domain\Model\Settings;
use WebVision\WvDeepltranslate\Exception\SettingQueryException;

/**
 * @deprecated Module is deprecated v10 and remove with v12
 */
class SettingsRepository extends Repository
{
    public function initializeObject(): void
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);

        $this->setDefaultQuerySettings($querySettings);
    }

    public function insertDeeplSettings(int $pid, array $languagesAssigned): void
    {
        $settings = new Settings();
        $settings->setCreateDate((new \DateTime())->getTimestamp());
        $settings->setPid($pid);
        $settings->setLanguagesAssigned(serialize($languagesAssigned));

        $this->persistenceManager->add($settings);
        $this->persistenceManager->persistAll();
    }

    public function updateDeeplSettings(int $settingId, string $languagesAssigned): void
    {
        /** @var Settings|null $settings */
        $settings = $this->findByUid($settingId);
        if ($settings === null) {
            throw new SettingQueryException(sprintf('DeepL settings with id "%d" not available', $settingId), 1657963739);
        }

        $settings->setLanguagesAssigned($languagesAssigned);
        $this->persistenceManager->update($settings);
        $this->persistenceManager->persistAll();
    }

    public function getSettings(): ?Settings
    {
        /** @var QueryResultInterface<Settings> $result */
        $result = $this->findAll();

        if ($result->count() > 0) {
            /** @var Settings $settings */
            $settings = $result->getFirst();
            return $settings;
        }

        return null;
    }

    /**
     * Get language mappings for a sys_language
     *
     * @return string
     */
    public function getMappings(int $uid): string
    {
        $settings = $this->getSettings();
        if ($settings === null) {
            return '';
        }

        $assignments = $settings->getLanguagesAssigned();
        if (empty($assignments)) {
            return '';
        }

        if (!isset($assignments[$uid])) {
            return '';
        }

        return $assignments[$uid];
    }

    /**
     * Merges default supported languages with language mappings
     *
     * @param array $apiSupportedLanguages
     * @return array
     */
    public function getSupportedLanguages(array $apiSupportedLanguages): array
    {
        $settings = $this->getSettings();
        if ($settings === null) {
            return $apiSupportedLanguages;
        }

        $languages = $settings->getLanguagesAssigned();

        foreach ($languages as $language) {
            if (!in_array($language, $apiSupportedLanguages)) {
                $apiSupportedLanguages[] = $language;
            }
        }

        return $apiSupportedLanguages;
    }
}
