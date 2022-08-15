<?php declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Settings extends AbstractEntity
{
    protected string $languagesAssigned = '';

    protected int $createDate = 0;

    public function getLanguagesAssigned(): array
    {
        $languagesAssigned = unserialize($this->languagesAssigned);
        if (is_array($languagesAssigned)) {
            return $languagesAssigned;
        }

        return [];
    }

    public function setLanguagesAssigned(string $serializeLanguagesAssigned): void
    {
        $this->languagesAssigned = $serializeLanguagesAssigned;
    }

    public function getCreateDate(): \DateTime
    {
        return (new \DateTime())->setTimestamp($this->createDate);
    }

    public function setCreateDate(int $createDate): void
    {
        $this->createDate = $createDate;
    }
}
