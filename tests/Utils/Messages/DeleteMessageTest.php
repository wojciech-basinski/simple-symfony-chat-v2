<?php declare(strict_types = 1);

namespace Tests\Utils\Messages;

use AppBundle\Entity\Message;
use AppBundle\Entity\User;
use AppBundle\Repository\MessageRepository;
use AppBundle\Utils\Messages\DeleteMessage;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ServerBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DeleteMessageTest extends TestCase
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
     * @var MockObject & RequestStack
     */
    private $requestStack;
    /**
     * @var MockObject & Request
     */
    private $request;
    /**
     * @var DeleteMessage
     */
    private $deleteMessage;
    /**
     * @var MockObject & MessageRepository
     */
    private $messageRepository;

    protected function setUp()
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->request = $this->createMock(Request::class);
        $this->messageRepository = $this->createMock(MessageRepository::class);

        $this->deleteMessage = new DeleteMessage($this->em, $this->session, $this->requestStack);
    }

    public function testDeleteMessageWithNullMessage()
    {
        $user = new User();
        $this->em->method('getRepository')
            ->willReturn($this->messageRepository);
        $this->messageRepository->method('find')
            ->with(1)
            ->willReturn(null);
        $this->assertSame(0, $this->deleteMessage->deleteMessage(1, $user));
    }

    public function testDeleteMessageThrowsException()
    {
        $user = new User();
        $this->em->method('getRepository')
            ->willReturn($this->messageRepository);
        $this->messageRepository->method('find')
            ->with(1)
            ->willReturn(new Message());
        $this->requestStack->method('getCurrentRequest')
            ->willReturn(null);
        $this->em->expects($this->once())
            ->method('remove');
        $this->em->expects($this->once())
            ->method('flush');

        $this->expectException(RuntimeException::class);

        $this->deleteMessage->deleteMessage(1, $user);
    }

    public function testDeleteMessageWithMessage()
    {
        $serverBag = $this->createMock(ServerBag::class);
        $user = new User();
        $this->session->method('get')
            ->with('channel')
            ->willReturn(1);
        $this->em->method('getRepository')
            ->willReturn($this->messageRepository);
        $this->messageRepository->method('find')
            ->with(1)
            ->willReturn(new Message());
        $this->requestStack->method('getCurrentRequest')
            ->willReturn($this->request);
        $this->request->server = $serverBag;
        $serverBag->method('get')
            ->with('REMOTE_ADDR')
            ->willReturn('127.0.0.1');
        $this->em->expects($this->once())
            ->method('remove');
        $this->em->expects($this->exactly(2))
            ->method('flush');
        $this->em->expects($this->once())
            ->method('persist');
        $this->deleteMessage->deleteMessage(1, $user);
    }
}