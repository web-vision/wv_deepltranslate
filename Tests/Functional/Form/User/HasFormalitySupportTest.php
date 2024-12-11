<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core\Tests\Functional\Form\User;

use TYPO3\CMS\Backend\Form\FormDataProvider\EvaluateDisplayConditions;
use WebVision\Deepltranslate\Core\Form\User\HasFormalitySupport;
use WebVision\Deepltranslate\Core\Tests\Functional\AbstractDeepLTestCase;

/**
 * @covers \WebVision\Deepltranslate\Core\Form\User\HasFormalitySupport
 */
class HasFormalitySupportTest extends AbstractDeepLTestCase
{
    protected function setUp(): void
    {
        $this->configurationToUseInTestInstance = array_merge(
            $this->configurationToUseInTestInstance,
            require __DIR__ . '/../../Fixtures/ExtensionConfig.php'
        );

        parent::setUp();
    }

    /**
     * @test
     */
    public function hasFormalitySupportIsSupported(): void
    {
        /** @var HasFormalitySupport $subject */
        $subject = $this->getContainer()->get(HasFormalitySupport::class);
        $evaluateDisplayConditionsMock = $this->createMock(EvaluateDisplayConditions::class);

        $isFormalitySupported = $subject->checkFormalitySupport([
            'record' => [
                'deeplTargetLanguage' => [
                    'DE',
                ],
            ],
        ], $evaluateDisplayConditionsMock);

        static::assertTrue($isFormalitySupported);
    }

    /**
     * @test
     */
    public function hasFormalitySupportIsNotSupported(): void
    {
        /** @var HasFormalitySupport $subject */
        $subject = $this->getContainer()->get(HasFormalitySupport::class);
        $evaluateDisplayConditionsMock = $this->createMock(EvaluateDisplayConditions::class);

        $isFormalitySupported = $subject->checkFormalitySupport([
            'record' => [
                'deeplTargetLanguage' => [
                    'EN-GB',
                ],
            ],
        ], $evaluateDisplayConditionsMock);

        static::assertFalse($isFormalitySupported);
    }

    /**
     * @test
     */
    public function formalityIsNotSupportedWhenRecordNotExist(): void
    {
        /** @var HasFormalitySupport $subject */
        $subject = $this->getContainer()->get(HasFormalitySupport::class);
        $evaluateDisplayConditionsMock = $this->createMock(EvaluateDisplayConditions::class);

        $isFormalitySupported = $subject->checkFormalitySupport([], $evaluateDisplayConditionsMock);

        static::assertFalse($isFormalitySupported);
    }

    /**
     * @test
     */
    public function formalityIsNotSupportedWhenDeeplTargetLanguageNotExistOrEmpty(): void
    {
        /** @var HasFormalitySupport $subject */
        $subject = $this->getContainer()->get(HasFormalitySupport::class);
        $evaluateDisplayConditionsMock = $this->createMock(EvaluateDisplayConditions::class);

        $isFormalitySupported = $subject->checkFormalitySupport([
            'record' => [
                'deeplTargetLanguage' => [],
            ],
        ], $evaluateDisplayConditionsMock);

        static::assertFalse($isFormalitySupported);
    }
}
