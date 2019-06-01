<?php declare(strict_types = 1);

namespace Tests\Utils\Messages\SpecialMessages\Create;

use AppBundle\Entity\User;
use AppBundle\Entity\UserOnline;
use AppBundle\Repository\UserOnlineRepository;
use AppBundle\Repository\UserRepository;
use AppBundle\Utils\ChatConfig;
use AppBundle\Utils\Messages\Database\AddMessageToDatabase;
use AppBundle\Utils\Messages\SpecialMessages\Create\BanUserCreate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class BanUserCreateTest extends TestCase
{
    /**
     * @var MockObject & TranslatorInterface
     */
    private $translator;
    /**
     * @var EntityManagerInterface & MockObject
     */
    private $em;
    /**
     * @var SessionInterface & MockObject
     */
    private $session;
    /**
     * @var AuthorizationCheckerInterface & MockObject
     */
    private $auth;
    /**
     * @var AddMessageToDatabase & MockObject
     */
    private $addMessageToDatabase;
    /**
     * @var ChatConfig & MockObject
     */
    private $config;
    /**
     * @var UserRepository & MockObject
     */
    private $userRepository;
    /**
     * @var BanUserCreate
     */
    private $banUserCreate;
    /**
     * @var UserOnlineRepository & MockObject
     */
    private $userOnlineRepository;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->auth = $this->createMock(AuthorizationCheckerInterface::class);
        $this->addMessageToDatabase = $this->createMock(AddMessageToDatabase::class);
        $this->config = $this->createMock(ChatConfig::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->userOnlineRepository = $this->createMock(UserOnlineRepository::class);

        $this->translator->method('getLocale')
            ->willReturn('en');

        $this->banUserCreate = new BanUserCreate(
            $this->auth,
            $this->session,
            $this->translator,
            $this->em,
            $this->addMessageToDatabase,
            $this->config
        );
    }

    public function testAddPermissionsDenied(): void
    {
        $user = new User();
        $this->auth->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_MODERATOR', $user)
            ->willReturn(false);

        $this->translator->method('trans')
            ->with(
                'error.notPermittedToBan',
                [],
                'chat',
                'en'
            )->willReturn('notPermittedToBan');
        $this->session->expects($this->once())
            ->method('set')
            ->with(
                'errorMessage',
                'notPermittedToBan'
            );

        $this->assertFalse($this->banUserCreate->add([], $user, 6));
    }

    public function testAddWrongUsernameError(): void
    {
        $user = new User();
        $this->auth->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_MODERATOR', $user)
            ->willReturn(true);

        $this->translator->method('trans')
            ->with(
                'error.wrongUsername',
                [],
                'chat',
                'en'
            )->willReturn('wrongUsername');
        $this->session->expects($this->once())
            ->method('set')
            ->with(
                'errorMessage',
                'wrongUsername'
            );

        $this->assertFalse($this->banUserCreate->add([], $user, 6));
    }

    public function testAddUserNotFoundError(): void
    {
        $user = new User();
        $this->auth->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_MODERATOR', $user)
            ->willReturn(true);
        $this->em->method('getRepository')
            ->with(User::class)
            ->willReturn($this->userRepository);
        $this->userRepository->method('findOneBy')
            ->with(['username' => 'nick'])
            ->willReturn(null);

        $this->translator->method('trans')
            ->with(
                'error.userNotFound',
                ['chat.nick' => 'nick'],
                'chat',
                'en'
            )->willReturn('userNotFound');
        $this->session->expects($this->once())
            ->method('set')
            ->with(
                'errorMessage',
                'userNotFound'
            );

        $this->assertFalse($this->banUserCreate->add(['/ban', 'nick'], $user, 6));
    }

    public function testAddCantBanAdminError(): void
    {
        $user = new User();
        $userToBan = (new User())->setRoles(['ROLE_ADMIN']);
        $this->auth->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_MODERATOR', $user)
            ->willReturn(true);
        $this->em->method('getRepository')
            ->with(User::class)
            ->willReturn($this->userRepository);
        $this->userRepository->method('findOneBy')
            ->with(['username' => 'nick'])
            ->willReturn($userToBan);

        $this->translator->method('trans')
            ->with(
                'error.cantBanAdmin',
                [],
                'chat',
                'en'
            )->willReturn('cantBanAdmin');
        $this->session->expects($this->once())
            ->method('set')
            ->with(
                'errorMessage',
                'cantBanAdmin'
            );

        $this->assertFalse($this->banUserCreate->add(['/ban', 'nick'], $user, 6));
    }

    public function testAddCantBanYourselfError(): void
    {
        //it could be tested by mock $user and $userToBan and add them random id but this two users id are null so
        // if ($userToBan->getId() === $user->getId()) will return true
        $user = new User();
        $userToBan = new User();
        $this->auth->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_MODERATOR', $user)
            ->willReturn(true);
        $this->em->method('getRepository')
            ->with(User::class)
            ->willReturn($this->userRepository);
        $this->userRepository->method('findOneBy')
            ->with(['username' => 'nick'])
            ->willReturn($userToBan);

        $this->translator->method('trans')
            ->with(
                'error.cantBanYourself',
                [],
                'chat',
                'en'
            )->willReturn('cantBanYourself');
        $this->session->expects($this->once())
            ->method('set')
            ->with(
                'errorMessage',
                'cantBanYourself'
            );

        $this->assertFalse($this->banUserCreate->add(['/ban', 'nick'], $user, 6));
    }

    public function testAddWithEmptyUserOnline(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')
            ->willReturn(1);
        $userToBan = (new User())->setUsername('username');
        $this->auth->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_MODERATOR', $user)
            ->willReturn(true);
        $this->em->expects($this->at(0))
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($this->userRepository);
        $this->em->expects($this->at(2))
            ->method('getRepository')
            ->with(UserOnline::class)
            ->willReturn($this->userOnlineRepository);
        $this->userRepository->method('findOneBy')
            ->with(['username' => 'username'])
            ->willReturn($userToBan);
        $this->em->expects($this->once())
            ->method('persist');
        $this->em->expects($this->once())
            ->method('flush');
        $this->config->method('getUserPrivateMessageChannelId')
            ->with($user)
            ->willReturn(6);
        $this->addMessageToDatabase->expects($this->once())
            ->method('addBotMessage')
            ->with(
                '/banned username',
                6
            );

        $this->assertTrue($this->banUserCreate->add(['/ban', 'username', 6, 'reason'], $user, 6));
    }

    public function testAddWithUserOnline(): void
    {
        $userOnline = new UserOnline();
        $user = $this->createMock(User::class);
        $user->method('getId')
            ->willReturn(1);
        $userToBan = (new User())->setUsername('username');
        $this->auth->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_MODERATOR', $user)
            ->willReturn(true);
        $this->em->expects($this->at(0))
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($this->userRepository);
        $this->em->expects($this->at(2))
            ->method('getRepository')
            ->with(UserOnline::class)
            ->willReturn($this->userOnlineRepository);
        $this->userRepository->method('findOneBy')
            ->with(['username' => 'username'])
            ->willReturn($userToBan);
        $this->userOnlineRepository->method('findOneBy')
            ->willReturn($userOnline);
        $this->em->expects($this->once())
            ->method('persist');
        $this->em->expects($this->once())
            ->method('flush');
        $this->em->expects($this->once())
            ->method('remove');
        $this->config->method('getUserPrivateMessageChannelId')
            ->with($user)
            ->willReturn(6);
        $this->addMessageToDatabase->expects($this->once())
            ->method('addBotMessage')
            ->with(
                '/banned username',
                6
            );

        $this->assertTrue($this->banUserCreate->add(['/ban', 'username', 6, 'reason'], $user, 6));
    }
}
