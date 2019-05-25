<?php declare(strict_types = 1);

namespace Tests\Utils\Messages\Transformers;

use AppBundle\Utils\Messages\Factory\DisplayMessageServiceFactory;
use AppBundle\Utils\Messages\SpecialMessages\Display\RollMessageDisplay;
use AppBundle\Utils\Messages\Transformers\SpecialMessageDisplayTransformer;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class SpecialMessageDisplayTransformerTest extends TestCase
{
    /**
     * @var MockObject & DisplayMessageServiceFactory
     */
    private $displayMessageServiceFactory;
    /**
     * @var SpecialMessageDisplayTransformer
     */
    private $specialMessageDisplayTransformer;

    protected function setUp()
    {
        $this->displayMessageServiceFactory = $this->createMock(DisplayMessageServiceFactory::class);
        $this->specialMessageDisplayTransformer = new SpecialMessageDisplayTransformer($this->displayMessageServiceFactory);
    }

    public function testSpecialMessagesAddReturnsFail(): void
    {
        $this->displayMessageServiceFactory->method('getDisplayService')
            ->with('text')
            ->willReturn(null);

        $this->assertEquals(
            ['userId' => false],
            $this->specialMessageDisplayTransformer->specialMessagesDisplay('text')
        );
    }

    public function testSpecialMessagesAddReturnsBool(): void
    {
        $rollMessageDisplay = $this->createMock(RollMessageDisplay::class);
        $this->displayMessageServiceFactory->method('getDisplayService')
            ->with('text')
            ->willReturn($rollMessageDisplay);
        $rollMessageDisplay->expects($this->at(0))
            ->method('display')
            ->willReturn([]);

        $this->assertEquals([], $this->specialMessageDisplayTransformer->specialMessagesDisplay('text'));
    }
}