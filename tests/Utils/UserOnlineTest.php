<?php declare(strict_types = 1);

namespace Tests\Utils;

use AppBundle\Entity\User;
use AppBundle\Repository\UserOnlineRepository;
use AppBundle\Utils\ChatConfig;
use AppBundle\Utils\UserOnline;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UserOnlineTest extends TestCase
{
    /**
     * @var EntityManagerInterface & MockObject
     */
    private $em;
    /**
     * @var ChatConfig & MockObject
     */
    private $config;
    /**
     * @var SessionInterface & MockObject
     */
    private $session;
    /**
     * @var UserOnline
     */
    private $userOnlineService;
    /**
     * @var UserOnlineRepository & MockObject
     */
    private $userOnlineRepository;
    /**
     * @var int
     */
    private $channel;

    protected function setUp()
    {
        parent::setUp();
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->config = $this->createMock(ChatConfig::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->userOnlineRepository = $this->createMock(UserOnlineRepository::class);
        $this->userOnlineService = new UserOnline($this->em, $this->config, $this->session);
        $this->channel = random_int(1, 999);
    }

    public function testAddUserOnlineBannedUser(): void
    {
        $user = (new User())->setBanned(new DateTime('now +1 day'));
        $this->em->expects($this->never())
            ->method('getRepository');

        $this->assertEquals(
            1,
            $this->userOnlineService->addUserOnline($user, $this->channel)
        );
    }

    public function testAddUserOnlineWithFoundUser(): void
    {
        $user = new User();
        $this->em->method('getRepository')
            ->willReturn($this->userOnlineRepository);
        $this->userOnlineRepository->method('findOneBy')
            ->willReturn($user);

        $this->assertEquals(
            0,
            $this->userOnlineService->addUserOnline($user, $this->channel)
        );
    }

    public function testAddUserOnlineWithNewUserOnline(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')
            ->willReturn(1);
        $this->em->method('getRepository')
            ->willReturn($this->userOnlineRepository);
        $this->userOnlineRepository->method('findOneBy')
            ->willReturn(null);
        $this->session->expects($this->once())
            ->method('set')
            ->with('afk', false);
        $this->em->expects($this->once())
            ->method('persist');
        $this->em->expects($this->once())
            ->method('flush');

        $this->assertEquals(
            0,
            $this->userOnlineService->addUserOnline($user, $this->channel)
        );
    }

    public function testUpdateUserOnlineWithOutUserOnlineWithBannedUser(): void
    {
        $user = (new User())->setBanned(new DateTime('now +1 day'));
        $this->em->method('getRepository')
            ->willReturn($this->userOnlineRepository);
        $this->userOnlineRepository->method('findOneBy')
            ->willReturn(null);

        $this->assertEquals(
            1,
            $this->userOnlineService->updateUserOnline($user, $this->channel, false)
        );
    }

    public function testUpdateUserOnlineWithOutUserOnlineWithUser(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')
            ->willReturn(1);
        $this->em->method('getRepository')
            ->willReturn($this->userOnlineRepository);
        $this->userOnlineRepository->method('findOneBy')
            ->willReturn(null);

        $this->assertEquals(
            0,
            $this->userOnlineService->updateUserOnline($user, $this->channel, false)
        );
    }

    public function testUpdateUserOnlineWithUserOnline(): void
    {
        $user = new User();
        $userOnline = (new \AppBundle\Entity\UserOnline())
            ->setChannel(-1)
            ->setTyping(false);
        $this->em->method('getRepository')
            ->willReturn($this->userOnlineRepository);
        $this->userOnlineRepository->method('findOneBy')
            ->willReturn($userOnline);
        $this->em->expects($this->once())
            ->method('persist');
        $this->em->expects($this->once())
            ->method('flush');


        $this->userOnlineService->updateUserOnline($user, $this->channel, true);

        $this->assertEquals(
            $this->channel,
            $userOnline->getChannel()
        );
        $this->assertTrue($userOnline->getTyping());
    }

    public function testDeleteUserWhenLogout(): void
    {
        $userOnline = new \AppBundle\Entity\UserOnline();
        $this->em->method('getRepository')
            ->willReturn($this->userOnlineRepository);
        $this->userOnlineRepository->method('findOneBy')
            ->with(['userId' => 666])
            ->willReturn($userOnline);
        $this->em->expects($this->once())
            ->method('remove')
            ->with($userOnline);
        $this->em->expects($this->once())
            ->method('flush');

        $this->userOnlineService->deleteUserWhenLogout(666);
    }

    public function testGetOnlineUsers(): void
    {
        $user = (new User())
            ->setUsername('username')
            ->setRoles(['ROLE_MODERATOR']);
        $userOnline = (new \AppBundle\Entity\UserOnline())->setUserInfo($user)
            ->setTyping(true)
            ->setAfk(true)
            ->setUserId(15);
        $this->config->expects($this->once())
            ->method('getInactiveTime')
            ->willReturn(5);
        $this->em->method('getRepository')
            ->willReturn($this->userOnlineRepository);
        $this->userOnlineRepository->expects($this->once())
            ->method('deleteInactiveUsers');
        $this->userOnlineRepository->method('findAllOnlineUserExceptUser')
            ->with(1, $this->channel)
            ->willReturn([$userOnline]);

        $this->assertEquals(
            [
                0 => [
                    'user_id' => 15,
                    'username' => 'username',
                    'user_role' => 'moderator',
                    'typing' => true,
                    'afk' => true
                ]
            ],
            $this->userOnlineService->getOnlineUsers(1, $this->channel)
        );
    }
}
