<?php declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Settings extends AbstractEntity
{
    protected string $languagesAssigned = '';

    public function getLanguagesAssigned(): array
    {
        return unserialize($this->languagesAssigned);
    }

    public function setLanguagesAssigned(array $languagesAssigned): void
    {
        $this->languagesAssigned = serialize($languagesAssigned);
    }
}
