<?php declare(strict_types = 1);


namespace Tests\Utils\Messages\Validator;

use AppBundle\Entity\Message;
use AppBundle\Entity\User;
use AppBundle\Utils\Channel;
use AppBundle\Utils\Messages\Validator\MessageDisplayValidator;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class MessageDisplayValidatorTest extends TestCase
{
    /**
     * @var MockObject & Channel
     */
    private $channel;
    /**
     * @var MessageDisplayValidator
     */
    private $messageDisplayValidator;

    protected function setUp()
    {
        $this->channel = $this->createMock(Channel::class);
        $this->messageDisplayValidator = new MessageDisplayValidator($this->channel);
    }

    public function testCheckIfMessagesCanBeDisplayed(): void
    {
        $user = new User();
        $this->channel->expects($this->at(0))
            ->method('checkIfUserCanBeOnThatChannel')
            ->willReturn(false);
        $this->channel->method('checkIfUserCanBeOnThatChannel')
            ->willReturn(true);
        $this->assertCount(
            0,
            $this->messageDisplayValidator->checkIfMessagesCanBeDisplayed($this->createMessageDelete(), $user)
        );
        $this->assertCount(
            0,
            $this->messageDisplayValidator->checkIfMessagesCanBeDisplayed($this->createMessageWithChannel(), $user)
        );
        $this->assertCount(
            3,
            $this->messageDisplayValidator->checkIfMessagesCanBeDisplayed($this->createMessages(), $user)
        );
    }

    /**
     * @return Message[]
     */
    private function createMessageDelete(): array
    {
        return [
            0 => [
                'text' => '/delete 50'
            ]
        ];
    }

    private function createMessageWithChannel(): array
    {
        return [
            0 => [
                'text' => 'text',
                'channel' => '60'
            ]
        ];
    }

    private function createMessages(): array
    {
        return [
            0 => [
                'text' => 'text',
                'channel' => 60
            ],
            1 => [
                'text' => '/delete 50',
                'channel' => 1
            ],
            2 => [
                'text' => 'text',
                'channel' => 1
            ],
            3 => [
                'text' => 'text',
                'channel' => 1
            ],
        ];
    }
}