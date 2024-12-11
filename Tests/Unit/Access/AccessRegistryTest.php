<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Tests\Unit\Access;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use WebVision\Deepltranslate\Core\Access\AccessItemInterface;
use WebVision\Deepltranslate\Core\Access\AccessRegistry;

/**
 * @covers \WebVision\Deepltranslate\Core\Access\AccessRegistry.php
 */
class AccessRegistryTest extends UnitTestCase
{
    /**
     * @test
     */
    public function registerAccessStoresTheAccessCorrectly(): void
    {
        $accessRegistry = new AccessRegistry();

        $identifier = 'testIdentifier';

        $accessObject = $this->createMock(AccessItemInterface::class);
        $accessObject->expects(static::once())->method('getIdentifier')->willReturn($identifier);

        $accessRegistry->addAccess($accessObject);
        $object = $accessRegistry->getAccess($identifier);

        static::assertSame($accessObject, $object);
    }

    /**
     * @test
     */
    public function getAccessReturnsNullForNonExistentIdentifier(): void
    {
        $accessRegistry = new AccessRegistry();

        static::assertNull($accessRegistry->getAccess('nonExistentIdentifier'));
    }
}
