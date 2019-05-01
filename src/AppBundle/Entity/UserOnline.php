<?php declare(strict_types = 1);

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

    public function getChannel(): int
    {
        return $this->channel;
    }

    public function setChannel(int $channel): UserOnline
    {
        $this->channel = $channel;

        return $this;
    }

    public function getUserName(): string
    {
        return $this->userInfo->getUsername();
    }

    public function getRole(): string
    {
        return $this->userInfo->getChatRoleAsText();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setUserId(int $userId): UserOnline
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setOnlineTime(\DateTime $onlineTime): UserOnline
    {
        $this->onlineTime = $onlineTime;

        return $this;
    }

    public function getUserInfo(): User
    {
        return $this->userInfo;
    }

    public function getOnlineTime(): \DateTime
    {
        return $this->onlineTime;
    }

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

    public function setTyping(bool $typing): UserOnline
    {
        $this->typing = $typing;

        return $this;
    }

    public function getTyping(): bool
    {
        return $this->typing;
    }

    public function setAfk(bool $afk): UserOnline
    {
        $this->afk = $afk;
        return $this;
    }

    public function getAfk(): bool
    {
        return $this->afk;
    }
}
