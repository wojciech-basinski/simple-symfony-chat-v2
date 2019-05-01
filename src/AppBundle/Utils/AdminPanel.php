<?php declare(strict_types = 1);

namespace AppBundle\Utils;

use Doctrine\ORM\EntityManagerInterface;

class AdminPanel
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * AdminPanel constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Gets all user from database
     *
     * @return array Array of Users entities
     */
    public function getAllUsers(): array
    {
        return $this->em->getRepository('AppBundle:User')->findAll();
    }

    /**
     * Changes User role on chat
     *
     * @param int $id User's id
     *
     * @param string $role role that User will have after changing
     */
    public function changeUsersRole(int $id, string $role): void
    {
        $user = $this->em->getRepository('AppBundle:User')->find($id);
        if ($user === null) {
            return;
        }

        $user->changeRole($role);

        $this->em->persist($user);
        $this->em->flush();
    }
}
