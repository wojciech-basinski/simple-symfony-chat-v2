<?php declare(strict_types = 1);

namespace Tests\Utils\Messages;

use AppBundle\Entity\User;
use AppBundle\Utils\Messages\AddMessage;
use AppBundle\Utils\Messages\Database\AddMessageToDatabase;
use AppBundle\Utils\Messages\Transformers\NewLineTransformer;
use AppBundle\Utils\Messages\Transformers\SpecialMessageAddTransformer;
use AppBundle\Utils\Messages\Validator\AddMessageValidator;
use AppBundle\Utils\Messages\Validator\UserAfkValidator;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AddMessageTest extends TestCase
{
    /**
     * @var MockObject & AddMessageValidator
     */
    private $addMessageValidator;
    /**
     * @var MockObject & SessionInterface
     */
    private $session;
    /**
     * @var MockObject & UserAfkValidator
     */
    private $userAfkValidator;
    /**
     * @var MockObject & SpecialMessageAddTransformer
     */
    private $specialMessageAddTransformer;
    /**
     * @var MockObject & AddMessageToDatabase
     */
    private $addMessageToDatabase;
    /**
     * @var MockObject & NewLineTransformer
     */
    private $newLineTransformer;
    /**
     * @var AddMessage
     */
    private $addMessage;

    protected function setUp()
    {
        $this->addMessageValidator = $this->createMock(AddMessageValidator::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->userAfkValidator = $this->createMock(UserAfkValidator::class);
        $this->specialMessageAddTransformer = $this->createMock(SpecialMessageAddTransformer::class);
        $this->addMessageToDatabase = $this->createMock(AddMessageToDatabase::class);
        $this->newLineTransformer = $this->createMock(NewLineTransformer::class);

        $this->addMessage = new AddMessage(
            $this->addMessageValidator,
            $this->session,
            $this->userAfkValidator,
            $this->specialMessageAddTransformer,
            $this->addMessageToDatabase,
            $this->newLineTransformer
        );
    }

    public function testAddMessageToDatabaseWithInvalidData(): void
    {
        $user = new User();
        $this->addMessageValidator->method('validateMessage')
            ->with($user, 1, 'some text')
            ->willReturn(false);

        $this->assertEquals(
            [
                'status' => 'false',
                'errorMessage' => null
            ],
            $this->addMessage->addMessageToDatabase($user, 'some text', 1)
        );
    }

    public function testAddMessageToDatabaseWithUserAfk()
    {
        $user = new User();
        $this->addMessageValidator->method('validateMessage')
            ->with($user, 1, 'some text')
            ->willReturn(true);
        $this->userAfkValidator->method('validateUserAfk')
            ->willReturn(true);
        $this->specialMessageAddTransformer->expects($this->at(0))
            ->method('specialMessagesAdd')
            ->with('/afk', $user, 1);
        $this->addMessageToDatabase->expects($this->once())
            ->method('addMessage');

        $this->assertEquals(
            ['status' => 'true'],
            $this->addMessage->addMessageToDatabase($user, 'some text', 1)
        );
    }

    public function testAddMessageToDatabaseWithUserAfkAndSpecialMessage()
    {
        $user = new User();
        $this->addMessageValidator->method('validateMessage')
            ->with($user, 1, 'some text')
            ->willReturn(true);
        $this->userAfkValidator->method('validateUserAfk')
            ->willReturn(false);
        $this->specialMessageAddTransformer->method('specialMessagesAdd')
            ->willReturn(true);
        $this->addMessageToDatabase->expects($this->never())
            ->method('addMessage');

        $this->assertEquals(
            ['status' => 'true'],
            $this->addMessage->addMessageToDatabase($user, 'some text', 1)
        );
    }

    public function testAddMessageToDatabaseWithUserAfkAndNoSpecialMessage()
    {
        $user = new User();
        $this->addMessageValidator->method('validateMessage')
            ->with($user, 1, 'some text')
            ->willReturn(true);
        $this->userAfkValidator->method('validateUserAfk')
            ->willReturn(false);
        $this->specialMessageAddTransformer->method('specialMessagesAdd')
            ->willReturn(false);
        $this->addMessageToDatabase->expects($this->never())
            ->method('addMessage');

        $this->assertEquals(
            [
                'status' => 'false',
                'errorMessage' => null
            ],
            $this->addMessage->addMessageToDatabase($user, 'some text', 1)
        );
    }
}
