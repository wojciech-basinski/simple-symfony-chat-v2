<?php declare(strict_types = 1);

namespace Tests\Utils\Messages\Transformers;

use AppBundle\Entity\User;
use AppBundle\Utils\Messages\Factory\AddMessageServiceFactory;
use AppBundle\Utils\Messages\SpecialMessages\Create\RollMessageCreate;
use AppBundle\Utils\Messages\Transformers\SpecialMessageAddTransformer;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class SpecialMessageAddTransformerTest extends TestCase
{
    /**
     * @var MockObject & AddMessageServiceFactory
     */
    private $addMessageServiceFactory;
    /**
     * @var SpecialMessageAddTransformer
     */
    private $specialMessageAddTransformer;

    protected function setUp()
    {
        $this->addMessageServiceFactory = $this->createMock(AddMessageServiceFactory::class);
        $this->specialMessageAddTransformer = new SpecialMessageAddTransformer($this->addMessageServiceFactory);
    }

    public function testSpecialMessagesAddReturnsNull(): void
    {
        $user = new User();
        $this->addMessageServiceFactory->method('getAddService')
            ->with('text')
            ->willReturn(null);

        $this->assertNull($this->specialMessageAddTransformer->specialMessagesAdd('text', $user, 1));
    }

    public function testSpecialMessagesAddReturnsBool(): void
    {
        $user = new User();
        $rollMessageCreate = $this->createMock(RollMessageCreate::class);
        $this->addMessageServiceFactory->method('getAddService')
            ->with('text')
            ->willReturn($rollMessageCreate);
        $rollMessageCreate->expects($this->at(0))
            ->method('add')
            ->willReturn(false);
        $rollMessageCreate->expects($this->at(1))
            ->method('add')
            ->willReturn(true);

        $this->assertFalse($this->specialMessageAddTransformer->specialMessagesAdd('text', $user, 1));
        $this->assertTrue($this->specialMessageAddTransformer->specialMessagesAdd('text', $user, 1));
    }
}
