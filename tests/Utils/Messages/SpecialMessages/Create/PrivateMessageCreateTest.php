<?php declare(strict_types = 1);

namespace Tests\Utils\Messages\SpecialMessages\Create;

use AppBundle\Entity\User;
use AppBundle\Repository\UserRepository;
use AppBundle\Utils\ChatConfig;
use AppBundle\Utils\Messages\Database\AddMessageToDatabase;
use AppBundle\Utils\Messages\SpecialMessages\Create\PrivateMessageCreate;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;

class PrivateMessageCreateTest extends TestCase
{
    /**
     * @var MockObject & TranslatorInterface
     */
    private $translator;
    /**
     * @var MockObject & SessionInterface
     */
    private $session;
    /**
     * @var MockObject & AddMessageToDatabase
     */
    private $addMessageToDatabase;
    /**
     * @var MockObject & ChatConfig
     */
    private $config;
    /**
     * @var MockObject & EntityManagerInterface
     */
    private $em;
    /**
     * @var MockObject & UserRepository
     */
    private $userRepository;
    /**
     * @var PrivateMessageCreate
     */
    private $privateMessageCreate;

    protected function setUp()
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->addMessageToDatabase = $this->createMock(AddMessageToDatabase::class);
        $this->config = $this->createMock(ChatConfig::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->em->method('getRepository')
            ->willReturn($this->userRepository);

        $this->privateMessageCreate = new PrivateMessageCreate(
            $this->translator,
            $this->session,
            $this->addMessageToDatabase,
            $this->config,
            $this->em
        );
    }

    public function testAddWithWrongUsername(): void
    {
        $user = new User();
        $this->translator->method('getLocale')
            ->willReturn('en');
        $this->translator->method('trans')
            ->with('error.wrongUsername', [], 'chat', 'en')
            ->willReturn('wrong.username');
        $this->session->expects($this->once())
            ->method('set')
            ->with('errorMessage', 'wrong.username');

        $this->assertFalse($this->privateMessageCreate->add(['text'], $user, 1));
    }

    public function testAddWithNotFoundUser(): void
    {
        $user = new User();
        $this->userRepository->method('findOneBy')
            ->with(['username' => 'xxx'])
            ->willReturn(null);
        $this->translator->method('getLocale')
            ->willReturn('en');
        $this->translator->method('trans')
            ->with('error.wrongUsername', [], 'chat', 'en')
            ->willReturn('wrong.username');
        $this->session->expects($this->once())
            ->method('set')
            ->with('errorMessage', 'wrong.username');

        $this->assertFalse($this->privateMessageCreate->add(['text', 'xxx'], $user, 1));
    }

    public function testAddWithUser(): void
    {
        $user = new User();
        $user2 = new User();
        $this->userRepository->method('findOneBy')
            ->with(['username' => 'xxx'])
            ->willReturn($user2);
        $this->config->method('getUserPrivateMessageChannelId')
            ->willReturn(45);
        $this->addMessageToDatabase->expects($this->exactly(2))
            ->method('addMessage');

        $this->assertTrue($this->privateMessageCreate->add(['text', 'xxx yyy'], $user, 1));
    }

    public function testAddWithUserWithoutText(): void
    {
        $user = new User();
        $user2 = new User();
        $this->userRepository->method('findOneBy')
            ->with(['username' => 'xxx'])
            ->willReturn($user2);
        $this->addMessageToDatabase->expects($this->never())
            ->method('addMessage');
        $this->translator->method('getLocale')
            ->willReturn('en');
        $this->translator->method('trans')
            ->with('error.emptyPm', [], 'chat', 'en')
            ->willReturn('error.emptyPm');
        $this->session->expects($this->once())
            ->method('set')
            ->with('errorMessage', 'error.emptyPm');

        $this->assertFalse($this->privateMessageCreate->add(['text', 'xxx'], $user, 1));
    }
}