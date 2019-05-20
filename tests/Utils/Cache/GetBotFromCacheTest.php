<?php declare(strict_types = 1);

namespace Tests\Utils\Cache;

use AppBundle\Entity\User;
use AppBundle\Utils\Cache\GetBotUserFromCache;
use AppBundle\Utils\ChatConfig;
use Doctrine\ORM\EntityManagerInterface;
use \RuntimeException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\Cache\Simple\FilesystemCache;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class GetBotFromCacheTest extends TestCase
{
    /**
     * @var LoggerInterface & MockObject
     */
    private $logger;
    /**
     * @var EntityManagerInterface & MockObject
     */
    private $em;
    /**
     * @var ChatConfig & MockObject
     */
    private $config;
    /**
     * @var FilesystemCache & MockObject
     */
    private $cache;
    /**
     * @var GetBotUserFromCache
     */
    private $getBotUserFromCache;

    protected function setUp()
    {
        parent::setUp();
        $this->cache = $this->createMock(FilesystemCache::class);
        $this->config = $this->createMock(ChatConfig::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->getBotUserFromCache = new GetBotUserFromCache(
            $this->cache,
            $this->config,
            $this->em,
            $this->logger
        );
    }

    public function testGetChatBotUserFromCache(): void
    {
        $user = new User();
        $this->cache->method('get')
            ->with('botUser')
            ->willReturn($user);
        $this->logger->expects($this->once())
            ->method('debug')
            ->with('load bot user from cache');

        $this->assertEquals($user, $this->getBotUserFromCache->getChatBotUser());
    }

    public function testGetChatBotUserFromDatabase(): void
    {
        $user = new User();
        $this->cache->method('get')
            ->with('botUser')
            ->willReturn(null);

        $this->config->method('getBotId')
            ->willReturn(1);

        $this->em->method('find')
            ->with(User::class, 1)
            ->willReturn($user);

        $this->logger->expects($this->once())
            ->method('info')
            ->with('set bot to cache');

        $this->assertEquals($user, $this->getBotUserFromCache->getChatBotUser());
    }

    public function testGetChatBotUserThrowsException(): void
    {
        $this->cache->method('get')
            ->with('botUser')
            ->willReturn(null);

        $this->config->method('getBotId')
            ->willReturn(1);

        $this->em->method('find')
            ->with(User::class, 1)
            ->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->getBotUserFromCache->getChatBotUser();
    }
}
