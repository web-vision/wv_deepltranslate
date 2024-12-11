<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Access;

use TYPO3\CMS\Core\SingletonInterface;

final class AccessRegistry implements SingletonInterface
{
    /**
     * @var AccessItemInterface[]
     */
    private static $access = [];

    public function addAccess(AccessItemInterface $accessItem): void
    {
        self::$access[] = $accessItem;
    }

    /**
     * @return AccessItemInterface[]
     */
    public function getAllAccess(): array
    {
        return self::$access;
    }

    public function getAccess(string $identifier): ?AccessItemInterface
    {
        foreach (self::$access as $accessItem) {
            if ($accessItem->getIdentifier() === $identifier) {
                return $accessItem;
            }
        }

        return null;
    }

    public function hasAccess(string $identifier): bool
    {
        $object = $this->getAccess($identifier);
        return $object !== null;
    }
}
