<?php declare(strict_types = 1);

namespace Tests\Utils\Messages\SpecialMessages\Create;

use AppBundle\Entity\User;
use AppBundle\Entity\UserOnline;
use AppBundle\Repository\UserOnlineRepository;
use AppBundle\Utils\Messages\Database\AddMessageToDatabase;
use AppBundle\Utils\Messages\SpecialMessages\Create\AfkMessageCreate;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AfkMessageCreateTest extends TestCase
{
    /**
     * @var MockObject & EntityManagerInterface
     */
    private $em;
    /**
     * @var MockObject& SessionInterface
     */
    private $session;
    /**
     * @var MockObject & AddMessageToDatabase
     */
    private $addMessageToDatabase;
    /**
     * @var AfkMessageCreate
     */
    private $afkMessageCreate;
    /**
     * @var MockObject & UserOnlineRepository
     */
    private $userOnlineRepository;

    protected function setUp()
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->addMessageToDatabase = $this->createMock(AddMessageToDatabase::class);
        $this->userOnlineRepository = $this->createMock(UserOnlineRepository::class);
        $this->em->method('getRepository')
            ->willReturn($this->userOnlineRepository);

        $this->afkMessageCreate = new AfkMessageCreate($this->em, $this->session, $this->addMessageToDatabase);
    }

    public function testAddWithoutUserOnline(): void
    {
        $user = new User();
        $this->userOnlineRepository->method('findOneBy')
            ->willReturn(null);
        $this->session->expects($this->once())
            ->method('set')
            ->with('errorMessage', 'Error');

        $this->assertFalse($this->afkMessageCreate->add(['text'], $user, 1));
    }

    public function testAddWithoutAfkSetNoBotMessage(): void
    {
        $user = new User();
        $userOnline = new UserOnline();
        $this->userOnlineRepository->method('findOneBy')
            ->willReturn($userOnline);
        $this->em->expects($this->once())
            ->method('persist');
        $this->em->expects($this->once())
            ->method('flush');
        $this->addMessageToDatabase->expects($this->never())
            ->method('addBotMessage');

        $this->assertTrue($this->afkMessageCreate->add(['text', 'text'], $user, 1));
    }

    public function testAddWithoutAfkSetWithBotMessage(): void
    {
        $user = new User();
        $userOnline = new UserOnline();
        $this->userOnlineRepository->method('findOneBy')
            ->willReturn($userOnline);
        $this->em->expects($this->once())
            ->method('persist');
        $this->em->expects($this->once())
            ->method('flush');
        $this->addMessageToDatabase->expects($this->once())
            ->method('addBotMessage');

        $this->assertTrue($this->afkMessageCreate->add(['text'], $user, 1));
    }

    public function testAddWitAfkSetNoBotMessage(): void
    {
        $user = new User();
        $userOnline = (new UserOnline())->setAfk(true);
        $this->userOnlineRepository->method('findOneBy')
            ->willReturn($userOnline);
        $this->em->expects($this->once())
            ->method('persist');
        $this->em->expects($this->once())
            ->method('flush');
        $this->addMessageToDatabase->expects($this->never())
            ->method('addBotMessage');

        $this->assertTrue($this->afkMessageCreate->add(['text', 'text'], $user, 1));
    }

    public function testAddWitAfkSetWithBotMessage(): void
    {
        $user = new User();
        $userOnline = (new UserOnline())->setAfk(true);
        $this->userOnlineRepository->method('findOneBy')
            ->willReturn($userOnline);
        $this->em->expects($this->once())
            ->method('persist');
        $this->em->expects($this->once())
            ->method('flush');
        $this->addMessageToDatabase->expects($this->once())
            ->method('addBotMessage');

        $this->assertTrue($this->afkMessageCreate->add(['text'], $user, 1));
    }
}