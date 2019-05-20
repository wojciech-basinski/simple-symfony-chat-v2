<?php declare(strict_types = 1);

namespace Tests\Utils;

use AppBundle\Entity\Message;
use AppBundle\Entity\User;
use AppBundle\Repository\MessageRepository;
use AppBundle\Repository\UserRepository;
use AppBundle\Utils\ChatConfig;
use AppBundle\Utils\Logs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class LogsTest extends TestCase
{
    /**
     * @var ChatConfig & MockObject
     */
    private $config;
    /**
     * @var EntityManagerInterface & MockObject
     */
    private $em;
    /**
     * @var Logs
     */
    private $logsService;
    /**
     * @var MessageRepository & MockObject
     */
    private $messageRepository;
    /**
     * @var UserRepository & MockObject
     */
    private $userRepository;


    protected function setUp()
    {
        parent::setUp();
        $this->config = $this->createMock(ChatConfig::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->messageRepository = $this->createMock(MessageRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->logsService = new Logs($this->em, $this->config);
    }

    public function testGetLogsWithEmptyUserName(): void
    {
        $message1 = new Message();
        $message2 = new Message();
        $this->em->method('getRepository')
            ->with(Message::class)
            ->willReturn($this->messageRepository);
        $this->messageRepository->method('findBetweenTwoDates')
            ->willReturn([$message1, $message2]);

        $this->assertEquals(
            [$message1, $message2],
            $this->logsService->getLogs('start', 'end', '')
        );
    }

    public function testGetLogsWithEmptyUserNameAndEndDateAfterStartDate(): void
    {
        $message1 = new Message();
        $message2 = new Message();
        $this->em->method('getRepository')
            ->with(Message::class)
            ->willReturn($this->messageRepository);
        $this->messageRepository->method('findBetweenTwoDates')
            ->willReturn([$message1, $message2]);

        $this->assertEquals(
            [$message1, $message2],
            $this->logsService->getLogs('01.01.2010 14:10', '31.12.2009 19:10', '')
        );
    }

    public function testGetLogsWithUserName(): void
    {
        $message1 = new Message();
        $message2 = new Message();
        $user = new User();
        $this->em->expects($this->at(0))
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($this->userRepository);
        $this->em->expects($this->at(1))
            ->method('getRepository')
            ->with(Message::class)
            ->willReturn($this->messageRepository);
        $this->userRepository->method('__call')
            ->with(
                $this->equalTo('findOneByUsername'),
                $this->equalTo(['username'])
            )
            ->willReturn($user);
        $this->messageRepository->method('findBetweenTwoDates')
            ->willReturn([$message1, $message2]);

        $this->assertEquals(
            [$message1, $message2],
            $this->logsService->getLogs('start', 'end', 'username')
        );
    }
}
