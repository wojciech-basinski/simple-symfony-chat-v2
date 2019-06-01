<?php declare(strict_types = 1);

namespace Tests\Utils\Messages\SpecialMessages\Create;

use AppBundle\Entity\User;
use AppBundle\Repository\UserRepository;
use AppBundle\Utils\ChatConfig;
use AppBundle\Utils\Messages\Database\AddMessageToDatabase;
use AppBundle\Utils\Messages\SpecialMessages\Create\BanListCreate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class BanListCreateTest extends TestCase
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
     * @var BanListCreate
     */
    private $banListCreate;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->auth = $this->createMock(AuthorizationCheckerInterface::class);
        $this->addMessageToDatabase = $this->createMock(AddMessageToDatabase::class);
        $this->config = $this->createMock(ChatConfig::class);
        $this->userRepository = $this->createMock(UserRepository::class);

        $this->banListCreate = new BanListCreate(
            $this->translator,
            $this->em,
            $this->session,
            $this->auth,
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
        $this->translator->method('getLocale')
            ->willReturn('en');
        $this->translator->method('trans')
            ->with(
                'error.notPermittedToListBan',
                [],
                'chat',
                'en'
            )->willReturn('notPermittedToListBan');
        $this->session->expects($this->once())
            ->method('set')
            ->with(
                'errorMessage',
                'notPermittedToListBan'
            );

        $this->assertFalse($this->banListCreate->add([], $user, 6));
    }

    public function testAdd(): void
    {
        $user = new User();
        $bannedUser = (new User())->setUsername('bannedUser');
        $this->auth->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_MODERATOR', $user)
            ->willReturn(true);
        $this->em->method('getRepository')
            ->with(User::class)
            ->willReturn($this->userRepository);
        $this->userRepository->method('getBannedUsers')
            ->willReturn([$bannedUser]);
        $this->config->method('getUserPrivateMessageChannelId')
            ->with($user)
            ->willReturn(666);
        $this->addMessageToDatabase->expects($this->once())
            ->method('addBotMessage')
            ->with(
                '/banlist bannedUser',
                666
            );

        $this->assertTrue($this->banListCreate->add([], $user, 6));
    }
}
