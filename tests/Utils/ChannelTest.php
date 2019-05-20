<?php declare(strict_types = 1);

namespace Tests\Utils;

use AppBundle\Entity\User;
use AppBundle\Utils\Channel;
use AppBundle\Utils\ChatConfig;
use AppBundle\Utils\UserOnline;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ChannelTest extends TestCase
{
    /**
     * @var ChatConfig & MockObject
     */
    private $config;
    /**
     * @var SessionInterface & MockObject
     */
    private $session;
    /**
     * @var UserOnline & MockObject
     */
    private $userOnline;
    /**
     * @var LoggerInterface & MockObject
     */
    private $logger;
    /**
     * @var Channel
     */
    private $channelService;

    protected function setUp()
    {
        parent::setUp();
        $this->config = $this->createMock(ChatConfig::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->userOnline = $this->createMock(UserOnline::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->channelService = new Channel(
            $this->config,
            $this->session,
            $this->userOnline,
            $this->logger
        );
    }

    public function testCheckIfUserCanBeOnThatChannel(): void
    {
        $user = new User();
        $this->config->method('getUserPrivateMessageChannelId')
            ->willReturn(55);
        $this->config->method('getChannels')
            ->willReturn([1 => 'a', 2 => 'b', 3 => 'c']);

        $this->assertTrue($this->channelService->checkIfUserCanBeOnThatChannel($user, 55));

        $this->assertTrue($this->channelService->checkIfUserCanBeOnThatChannel($user, 1));
        $this->assertFalse($this->channelService->checkIfUserCanBeOnThatChannel($user, 20));
    }

    public function testChangeChannelOnChat(): void
    {
        $user = new User();
        $this->config->method('getUserPrivateMessageChannelId')
            ->willReturn(55);
        $this->config->method('getChannels')
            ->willReturn([1 => 'a', 2 => 'b', 3 => 'c']);

        $this->assertFalse($this->channelService->changeChannelOnChat($user, 20));

        $this->userOnline->expects($this->once())
            ->method('updateUserOnline');
        $this->session->expects($this->exactly(2))
            ->method('set');

        $this->assertTrue($this->channelService->changeChannelOnChat($user, 55));
    }
}
