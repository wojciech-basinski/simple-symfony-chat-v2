<?php declare(strict_types = 1);

namespace Tests\Utils\Messages\Database;

use AppBundle\Entity\User;
use AppBundle\Repository\MessageRepository;
use AppBundle\Utils\Cache\GetBotUserFromCache;
use AppBundle\Utils\Messages\Database\AddMessageToDatabase;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ServerBag;

class AddMessageToDatabaseTest extends TestCase
{
    /**
     * @var MockObject & GetBotUserFromCache
     */
    private $getBotFromCache;
    /**
     * @var MockObject & EntityManagerInterface
     */
    private $em;
    /**
     * @var MockObject & RequestStack
     */
    private $requestStack;
    /**
     * @var MockObject & Request
     */
    private $request;
    /**
     * @var AddMessageToDatabase
     */
    private $addMessageToDatabase;
    /**
     * @var MockObject & MessageRepository
     */
    private $messageRepository;

    protected function setUp()
    {
        $this->getBotFromCache = $this->createMock(GetBotUserFromCache::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->request = $this->createMock(Request::class);
        $this->messageRepository = $this->createMock(MessageRepository::class);

        $this->addMessageToDatabase = new AddMessageToDatabase($this->getBotFromCache, $this->em, $this->requestStack);
    }

    public function testAddBotMessageWithoutRequest(): void
    {
        $this->requestStack->method('getCurrentRequest')
            ->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->addMessageToDatabase->addBotMessage('text', 1);
    }

    public function testAddBotMessageWithValidData(): void
    {
        $bot = new User();
        $this->requestStack->method('getCurrentRequest')
            ->willReturn($this->request);
        $server = $this->createMock(ServerBag::class);
        $server->method('get')
            ->with('REMOTE_ADDR')
            ->willReturn('111.111.111.111');
        $this->request->server = $server;
        $this->getBotFromCache->method('getChatBotUser')
            ->willReturn($bot);
        $this->em->method('getRepository')
            ->willReturn($this->messageRepository);
        $this->messageRepository->expects($this->once())
            ->method('addBotMessage')
            ->with(
                'some text',
                5,
                $bot,
                '111.111.111.111'
            );
        $this->addMessageToDatabase->addBotMessage('some text', 5);
    }

    public function testAddMessageWithoutRequest(): void
    {
        $user = new User();
        $this->requestStack->method('getCurrentRequest')
            ->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->addMessageToDatabase->addMessage('text', 1, $user);
    }

    public function testAddMessageWithValidData(): void
    {
        $user = new User();
        $this->requestStack->method('getCurrentRequest')
            ->willReturn($this->request);
        $server = $this->createMock(ServerBag::class);
        $server->method('get')
            ->with('REMOTE_ADDR')
            ->willReturn('111.111.111.111');
        $this->request->server = $server;

        $this->em->expects($this->once())
            ->method('persist');
        $this->em->expects($this->once())
            ->method('flush');

        $this->addMessageToDatabase->addMessage('some text', 5, $user);
    }
}