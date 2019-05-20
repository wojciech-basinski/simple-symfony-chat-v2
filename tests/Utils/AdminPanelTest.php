<?php declare(strict_types = 1);

namespace Tests\Utils;

use AppBundle\Entity\User;
use AppBundle\Repository\UserRepository;
use AppBundle\Utils\AdminPanel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class AdminPanelTest extends TestCase
{
    /**
     * @var EntityManagerInterface & MockObject
     */
    private $em;
    /**
     * @var UserRepository & MockObject
     */
    private $userRepository;
    /**
     * @var AdminPanel
     */
    private $adminPanel;

    protected function setUp()
    {
        parent::setUp();
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->adminPanel = new AdminPanel($this->em);
    }

    public function testGetAllUsers(): void
    {
        $user = new User();
        $user2 = new User();

        $this->em->method('getRepository')
            ->willReturn($this->userRepository);
        $this->userRepository->expects($this->at(0))
            ->method('findAll')
            ->willReturn([$user]);
        $this->userRepository->expects($this->at(1))
            ->method('findAll')
            ->willReturn([$user, $user2]);

        $this->assertEquals(
            [$user],
            $this->adminPanel->getAllUsers()
        );
        $this->assertEquals(
            [$user, $user2],
            $this->adminPanel->getAllUsers()
        );
    }

    public function testChangeUsersRoleWithNullUser(): void
    {
        $this->em->method('getRepository')
            ->willReturn($this->userRepository);
        $this->userRepository->method('find')
            ->willReturn(null);

        $this->em->expects($this->never())
            ->method('persist');
        $this->em->expects($this->never())
            ->method('flush');

        $this->adminPanel->changeUsersRole(1, 'role');
    }

    public function testChangeUsersRoleWithUser(): void
    {
        $user = new User();
        $this->em->method('getRepository')
            ->willReturn($this->userRepository);
        $this->userRepository->method('find')
            ->willReturn($user);

        $this->em->expects($this->once())
            ->method('persist');
        $this->em->expects($this->once())
            ->method('flush');

        $this->adminPanel->changeUsersRole(1, 'moderator');
        $this->assertEquals(
            'moderator',
            $user->getChatRoleAsText()
        );
    }
}
