<?php declare(strict_types = 1);

namespace Tests\Utils;

use AppBundle\Entity\User;
use AppBundle\Repository\UserRepository;
use AppBundle\Utils\Banned;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class BannedTest extends TestCase
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
     * @var Banned
     */
    private $bannedService;

    protected function setUp()
    {
        parent::setUp();
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->bannedService = new Banned($this->em);
    }

    public function testGetReason(): void
    {
        $user = (new User())->setBanReason('reason');
        $this->em->method('getRepository')
            ->willReturn($this->userRepository);
        $this->userRepository->method('findOneBy')
            ->with(['username' => 'username'])
            ->willReturn($user);

        $this->assertEquals(
            'reason',
            $this->bannedService->getReason('username')
        );
    }

    public function testGetTime(): void
    {
        $time = new DateTime('now');
        $user = (new User())->setBanned($time);

        $this->em->method('getRepository')
            ->willReturn($this->userRepository);
        $this->userRepository->method('findOneBy')
            ->with(['username' => 'username'])
            ->willReturn($user);

        $this->assertEquals(
            $time,
            $this->bannedService->getTime('username')
        );
    }

    public function testRemoveBan(): void
    {
        $time = new DateTime('now');
        $user = (new User())->setBanned($time)
            ->setBanReason('reason');

        $this->em->method('getRepository')
            ->willReturn($this->userRepository);
        $this->userRepository->method('findOneBy')
            ->with(['username' => 'username'])
            ->willReturn($user);
        $this->em->expects($this->once())
            ->method('persist');
        $this->em->expects($this->once())
            ->method('flush');

        $this->bannedService->removeBan('username');

        $this->assertNull($user->getBanReason());
        $this->assertNull($user->getBanned());
    }

    /**
     * covers Banned::getUser()
     */
    public function testGetUserPrivateMethodCallTwoTimes(): void
    {
        $time = new DateTime('now');
        $user = (new User())->setBanReason('reason')
            ->setBanned($time);

        $this->em->method('getRepository')
            ->willReturn($this->userRepository);
        $this->userRepository->method('findOneBy')
            ->with(['username' => 'username'])
            ->willReturn($user);

        $this->assertEquals(
            'reason',
            $this->bannedService->getReason('username')
        );

        $this->em->expects($this->once())
            ->method('persist');
        $this->em->expects($this->once())
            ->method('flush');

        $this->bannedService->removeBan('username');

        $this->assertNull($user->getBanReason());
        $this->assertNull($user->getBanned());
    }

    public function testGetUserWithNoUser(): void
    {
        $this->em->method('getRepository')
            ->willReturn($this->userRepository);
        $this->userRepository->method('findOneBy')
            ->with(['username' => 'xxxx'])
            ->willReturn(null);

        $this->expectException(RuntimeException::class);

        $this->bannedService->getReason('xxxx');
    }
}
