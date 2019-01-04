<?php

namespace AppBundle\Utils;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

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
     * UserOnline constructor.
     *
     * @param EntityManagerInterface $em
     * @param ChatConfig $config
     */
    public function __construct(EntityManagerInterface $em, ChatConfig $config)
    {
        $this->em = $em;
        $this->config = $config;
    }

    /**
     * Adds user's info to user_online table.
     *
     * @param User $user User instance
     * @param int $channel Channel's id
     * @return int
     */
    public function addUserOnline(User $user, int $channel): int
    {
        if ($user->getBanned()) {
            return 1;
        }
        if ( $this->em->getRepository('AppBundle:UserOnline')
            ->findOneBy([
                'userId' => $user->getId()
            ])
        ) {
            return 0;
        }

        $online = new \AppBundle\Entity\UserOnline();

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
     */
    public function updateUserOnline(User $user, int $channel, bool $typing): int
    {
        $online = $this->em->getRepository('AppBundle:UserOnline')
                    ->findOneBy([
                        'userId' => $user->getId()
                    ]);
        if (!$online) {
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
     */
    public function getOnlineUsers(int $id, int $channel)
    {
        $this->deleteInactiveUsers($id, $channel);
        $usersOnline = $this->em->getRepository('AppBundle:UserOnline')
            ->findAllOnlineUserExceptUser($id, $channel);

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
    public function deleteUserWhenLogout(int $id)
    {
        $online = $this->em->getRepository('AppBundle:UserOnline')
            ->findOneBy([
                'userId' => $id,
            ]);
        $this->em->remove($online);
        $this->em->flush();
    }

    /**
     * Delete Inactive Users from database except current User if user is inactive more
     * than inactive time from chat config
     *
     * @param int $id User's id
     * @param int $channel Channel's id
     */
    private function deleteInactiveUsers(int $id, int $channel)
    {
        $time = new \DateTime('now');
        $time->modify('-'.$this->config->getInactiveTime().'sec');

        $this->em->getRepository('AppBundle:UserOnline')
                ->deleteInactiveUsers($time, $id, $channel);
    }
}