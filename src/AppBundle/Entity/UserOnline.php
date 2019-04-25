<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserOnline
 *
 * @ORM\Table(name="user_online")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserOnlineRepository")
 */
class UserOnline
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer", unique=true)
     */
    private $userId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="online_time", type="datetime")
     */
    private $onlineTime;

    /**
     *
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userOnline", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $userInfo;

    /**
     * @var int
     *
     * @ORM\Column(name="channel", type="integer")
     */
    private $channel;

    /**
     * @var bool
     *
     * @ORM\Column(name="typing", type="boolean", nullable=true)
     */
    private $typing;

    /**
     * @var bool
     *
     * @ORM\Column(name="afk", type="boolean", nullable=false)
     */
    private $afk = false;

    /**
     * @return int Channel's id
     */
    public function getChannel(): int
    {
        return $this->channel;
    }

    /**
     * @param int $channel Channel's id
     *
     * @return UserOnline
     */
    public function setChannel(int $channel): UserOnline
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Gets User's username from relation
     *
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userInfo->getUsername();
    }

    /**
     * Gets User's role from relation
     *
     * @return string Return user's role as text
     */
    public function getRole(): string
    {
        return $this->userInfo->getChatRoleAsText();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set userId
     *
     * @param int $userId
     *
     * @return UserOnline
     */
    public function setUserId(int $userId): UserOnline
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Set onlineTime
     *
     * @param \DateTime $onlineTime
     *
     * @return UserOnline
     */
    public function setOnlineTime(\DateTime $onlineTime): UserOnline
    {
        $this->onlineTime = $onlineTime;

        return $this;
    }

    /**
     * @return User User's info from relation
     */
    public function getUserInfo(): User
    {
        return $this->userInfo;
    }

    /**
     * Get onlineTime
     *
     * @return \DateTime
     */
    public function getOnlineTime(): \DateTime
    {
        return $this->onlineTime;
    }

    /**
     * Set userInfo
     *
     * @param User $userInfo
     *
     * @return UserOnline
     */
    public function setUserInfo(User $userInfo): UserOnline
    {
        $this->userInfo = $userInfo;

        return $this;
    }

    /**
     * Create array with user's information: id, username and role
     *
     * @return array Array with information about user
     */
    public function createArrayToJson(): array
    {
        return [
            'user_id' => $this->userId,
            'username' => $this->userInfo->getUsername(),
            'user_role' => $this->userInfo->getChatRoleAsText(),
            'typing' => $this->typing,
            'afk' => $this->afk
        ];
    }

    /**
     * @param mixed $typing
     *
     * @return UserOnline
     */
    public function setTyping(bool $typing): UserOnline
    {
        $this->typing = $typing;

        return $this;
}

    /**
     * @return mixed
     */
    public function getTyping(): bool
    {
        return $this->typing;
    }

    /**
     * @param bool $afk
     *
     * @return UserOnline
     */
    public function setAfk(bool $afk): UserOnline
    {
        $this->afk = $afk;
        return $this;
    }

    /**
     * @return bool
     */
    public function getAfk(): bool
    {
        return $this->afk;
    }
}

