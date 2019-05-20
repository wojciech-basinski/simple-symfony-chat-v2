<?php declare(strict_types = 1);

namespace AppBundle\Utils;

use AppBundle\Entity\User;
use AppBundle\Repository\UserOnlineRepository;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\UserOnline as UserOnlineEntity;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Service to add, check and delete from Users online in database
 *
 * Class UserOnline
 * @package AppBundle\Utils
 */
class UserOnline
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var ChatConfig
     */
    private $config;
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var UserOnlineRepository
    */
    private $userOnlineRepository;

    public function __construct(EntityManagerInterface $em, ChatConfig $config, SessionInterface $session)
    {
        $this->em = $em;
        $this->config = $config;
        $this->session = $session;
        $this->initializeRepository();
    }

    /**
     * Adds user's info to user_online table.
     *
     * @param User $user User instance
     * @param int $channel Channel's id
     *
     * @return int
     * @throws \Exception
     */
    public function addUserOnline(User $user, int $channel): int
    {
        if ($user->getBanned()) {
            return 1;
        }
        if ($this->userOnlineRepository->findOneBy([
                'userId' => $user->getId()
            ])
        ) {
            return 0;
        }

        $this->session->set('afk', false);

        $online = new UserOnlineEntity();

        $online->setUserId($user->getId());
        $online->setOnlineTime(new \DateTime('now'));
        $online->setUserInfo($user);
        $online->setChannel($channel);

        $this->em->persist($online);
        $this->em->flush();
        return 0;
    }

    /**
     * Update User's Time in database - User will not be kicked for inactivity
     *
     * @param User $user User instance
     * @param int $channel Channel's id
     * @param bool $typing
     *
     * @return int
     * @throws \Exception
     */
    public function updateUserOnline(User $user, int $channel, bool $typing): int
    {
        /** @var UserOnlineEntity|null $online */
        $online = $this->userOnlineRepository->findOneBy([
                        'userId' => $user->getId()
                    ]);
        if ($online === null) {
            if ($this->addUserOnline($user, $channel)) {
                return 1;
            }
            return 0;
        }
        $online->setOnlineTime(new \DateTime('now'));
        $online->setChannel($channel);
        $online->setTyping($typing);

        $this->em->persist($online);
        $this->em->flush();
        return 0;
    }

    /**
     * Get array with online Users
     *
     * @param int $id User's id
     * @param int $channel Channel's id
     *
     * @return array Array of online Users
     * @throws \Exception
     */
    public function getOnlineUsers(int $id, int $channel): array
    {
        $this->deleteInactiveUsers($id, $channel);
        $usersOnline = $this->userOnlineRepository->findAllOnlineUserExceptUser($id, $channel);

        foreach ($usersOnline as &$user) {
            $user = $user->createArrayToJson();
        }

        return $usersOnline;
    }

    /**
     * Delete User's info from users online when logout from chat
     *
     * @param int $id User's id
     */
    public function deleteUserWhenLogout(int $id): void
    {
        $online = $this->userOnlineRepository->findOneBy([
                'userId' => $id,
            ]);
        if ($online === null) {
            return;
        }
        $this->em->remove($online);
        $this->em->flush();
    }

    /**
     * Delete Inactive Users from database except current User if user is inactive more
     * than inactive time from chat config
     *
     * @param int $id User's id
     * @param int $channel Channel's id
     *
     * @throws \Exception
     */
    private function deleteInactiveUsers(int $id, int $channel): void
    {
        $time = new \DateTime('now');
        $time->modify('-'.$this->config->getInactiveTime().'sec');

        $this->userOnlineRepository->deleteInactiveUsers($time, $id, $channel);
    }

    private function initializeRepository(): void
    {
        $repository = $this->em->getRepository(UserOnlineEntity::class);
        if (!$repository instanceof UserOnlineRepository) {
            throw new \RuntimeException('Could not find repository');
        }
        $this->userOnlineRepository = $repository;
    }
}
