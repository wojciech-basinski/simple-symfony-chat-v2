<?php declare(strict_types = 1);

namespace AppBundle\Utils;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class Banned
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var User|null
     */
    private $user;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getReason(string $userName): ?string
    {
        $user = $this->getUser($userName);
        return $user->getBanReason();
    }

    public function removeBan(string $userName): void
    {
        $user = $this->getUser($userName);
        $user->setBanned(null)
            ->setBanReason(null);
        $this->em->persist($user);
        $this->em->flush();
    }

    public function getTime(string $userName): ?\DateTime
    {
        $user = $this->getUser($userName);
        return $user->getBanned();
    }

    private function getUser(string $userName): User
    {
        if ($this->user !== null) {
            return $this->user;
        }
        /** @var User|null user */
        $user = $this->em->getRepository(User::class)->findOneBy(['username' => $userName]);
        if ($user === null) {
            throw new \RuntimeException('could not find user');
        }
        $this->user = $user;
        return $this->user;
    }
}
