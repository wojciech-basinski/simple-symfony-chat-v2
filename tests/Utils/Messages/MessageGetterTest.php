<?php declare(strict_types = 1);

namespace Tests\Utils\Messages;

use AppBundle\Entity\Message;
use AppBundle\Entity\User;
use AppBundle\Repository\MessageRepository;
use AppBundle\Utils\ChatConfig;
use AppBundle\Utils\Messages\MessageGetter;
use AppBundle\Utils\Messages\Transformers\MessageToArrayTransformer;
use AppBundle\Utils\Messages\Validator\MessageDisplayValidator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MessageGetterTest extends TestCase
{
    /**
     * @var MockObject & EntityManagerInterface
     */
    private $em;
    /**
     * @var MockObject & SessionInterface
     */
    private $session;
    /**
     * @var MockObject & ChatConfig
     */
    private $config;
    /**
     * @var MockObject & LoggerInterface
     */
    private $logger;
    /**
     * @var MockObject & MessageToArrayTransformer
     */
    private $messageTransformer;
    /**
     * @var MockObject & MessageDisplayValidator
     */
    private $messageDisplayValidator;
    /**
     * @var MessageGetter
     */
    private $messageGetter;
    /**
     * @var MockObject & MessageRepository
     */
    private $messageRepository;

    protected function setUp()
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->config = $this->createMock(ChatConfig::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->messageTransformer = $this->createMock(MessageToArrayTransformer::class);
        $this->messageDisplayValidator = $this->createMock(MessageDisplayValidator::class);
        $this->messageRepository = $this->createMock(MessageRepository::class);
        $this->em->method('getRepository')
            ->with(Message::class)
            ->willReturn($this->messageRepository);

        $this->messageGetter = new MessageGetter(
            $this->em,
            $this->session,
            $this->config,
            $this->logger,
            $this->messageTransformer,
            $this->messageDisplayValidator
        );
    }

    public function testGetMessagesInIndex(): void
    {
        $message1 = new Message();
        $message2 = new Message();
        $message3 = new Message();
        $user = new User();

        $this->session->method('get')
            ->with('channel')
            ->willReturn(1);
        $this->messageRepository->method('getMessagesFromLastDay')
            ->willReturn([$message1, $message2,$message3]);
        $this->messageRepository->method('getIdFromLastMessage')
            ->willReturn(50);
        $this->messageTransformer->method('transformMessagesToArray')
            ->with([$message1, $message2,$message3])
            ->willReturn([$message1, $message2]);
        $this->messageDisplayValidator->method('checkIfMessagesCanBeDisplayed')
            ->with([$message1, $message2])
            ->willReturn([$message2]);

        $this->assertEquals(
            [$message2],
            $this->messageGetter->getMessagesInIndex($user)
        );
    }

    public function testGetMessagesFromLastIdAfterChangeChannel(): void
    {
        $message1 = new Message();
        $message2 = new Message();
        $message3 = new Message();
        $user = new User();

        $this->session->expects($this->at(0))
            ->method('get')
            ->with('lastId')
            ->willReturn(1);
        $this->session->expects($this->at(1))
            ->method('get')
            ->with('changedChannel')
            ->willReturn(true);
        $this->session->expects($this->at(3))
            ->method('get')
            ->with('channel')
            ->willReturn(1);

        $this->messageRepository->method('getMessagesFromLastDay')
            ->willReturn([$message1, $message2,$message3]);
        $this->messageRepository->method('getIdFromLastMessage')
            ->willReturn(50);
        $this->messageTransformer->method('transformMessagesToArray')
            ->with([$message1, $message2, $message3])
            ->willReturn([$message1, $message2, $message3]);
        $this->messageDisplayValidator->method('checkIfMessagesCanBeDisplayed')
            ->with([$message1, $message2, $message3])
            ->willReturn([$message2, $message3]);

        $this->assertEquals(
            [$message2, $message3],
            $this->messageGetter->getMessagesFromLastId($user)
        );
    }

    public function testGetMessagesFromLastIdWithNotChangedChannel(): void
    {
        $message1 = new Message();
        $message2 = new Message();
        $message3 = $this->createMock(Message::class);
        $message3->method('getId')
            ->willReturn(2);
        $user = new User();

        $this->config->method('getPrivateMessageAdd')
            ->willReturn(15);
        $this->config->method('getUserPrivateMessageChannelId')
            ->willReturn(20);
        $this->session->expects($this->at(0))
            ->method('get')
            ->with('lastId')
            ->willReturn(1);
        $this->session->expects($this->at(1))
            ->method('get')
            ->with('changedChannel')
            ->willReturn(false);
        $this->session->expects($this->at(2))
            ->method('set')
            ->with('lastId', 2);
        $this->messageRepository->method('getMessagesFromLastId')
            ->with(1, 15, 20)
            ->willReturn([$message1, $message2, $message3]);
        $this->messageTransformer->method('transformMessagesToArray')
            ->with([$message1, $message2, $message3])
            ->willReturn([$message1, $message2,$message3]);
        $this->messageDisplayValidator->method('checkIfMessagesCanBeDisplayed')
            ->with([$message1, $message2, $message3])
            ->willReturn([$message1, $message2]);

        $this->assertEquals(
            [$message1, $message2],
            $this->messageGetter->getMessagesFromLastId($user)
        );
    }
}
