<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * @deprecated Module is deprecated v10 and remove with v12
 */
class Language extends AbstractEntity
{
    /**
     * @var string
     */
    protected string $title = '';

    /**
     * @var string
     */
    protected string $languageIsoCode = '';

    /**
     * @var string
     */
    protected string $flag = '';

    /**
     * @var int
     */
    protected int $staticLangIsoCode = 0;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getLanguageIsoCode(): string
    {
        return $this->languageIsoCode;
    }

    public function setLanguageIsoCode(string $languageIsoCode): void
    {
        $this->languageIsoCode = $languageIsoCode;
    }

    public function getFlag(): string
    {
        return $this->flag;
    }

    public function setFlag(string $flag): void
    {
        $this->flag = $flag;
    }

    public function getStaticLangIsoCode(): int
    {
        return $this->staticLangIsoCode;
    }

    public function setStaticLangIsoCode(int $staticLangIsoCode): void
    {
        $this->staticLangIsoCode = $staticLangIsoCode;
    }

    public function toArray(): array
    {
        return [
            'uid' => $this->uid,
            'title' => $this->title,
        ];
    }
}
