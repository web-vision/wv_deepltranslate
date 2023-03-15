<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Tests\Unit\Domain\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use WebVision\WvDeepltranslate\Domain\Model\Settings;

/**
 * @covers \WebVision\WvDeepltranslate\Domain\Model\Settings
 */
class SettingsTest extends UnitTestCase
{
    /** @test */
    public function hasExtbaseAbstractEntity(): void
    {
        $subject = new Settings();
        static::assertInstanceOf(AbstractEntity::class, $subject);
    }

    public function classAttributeDataProvider(): array
    {
        return [
            ['languagesAssigned', 'The entity "Settings" field has not the attribute "languagesAssigned"'],
        ];
    }

    /**
     * @dataProvider classAttributeDataProvider
     * @test
     */
    public function hasClassProperties(string $attribute, string $message): void
    {
        static::assertTrue(property_exists(Settings::class, $attribute), $message);
    }

    /** @test */
    public function checkFunctionalityFromLanguagesAssigned(): void
    {
        $subject = new Settings();
        $subject->setLanguagesAssigned(serialize(['Hello' => 'Welt']));

        $languagesAssigned = $subject->getLanguagesAssigned();
        static::assertIsArray($languagesAssigned);
        static::assertArrayHasKey('Hello', $languagesAssigned);
    }
}
