<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\UserInterface;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="message")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MessageRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Message
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
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text")
     */
    private $text;

    /**
     * @var int
     *
     * @ORM\Column(name="channel", type="integer")
     */
    private $channel;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userMessage", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $userInfo;

    /**
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    private $deletedAt = null;

    /**
     * @var string
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $ip;

    /**
     * @param User $userInfo User instance
     * @return $this
     */
    public function setUserInfo(User $userInfo)
    {
        $this->userInfo = $userInfo;

        return $this;
    }

    /**
     * @return string Username
     */
    public function getUsername(): string
    {
        return $this->userInfo->getUsername();
    }

    public function getUserAvatar(): string
    {
        return $this->userInfo->getAvatar();
    }

    /**
     * @return string Return user's role as text
     */
    public function getRole(): string
    {
        return $this->userInfo->getChatRoleAsText();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $userId
     * @return Message
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param \DateTime $date
     * @return Message
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $text
     * @return Message
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param integer $channel
     * @return Message
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return int
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return UserInterface
     */
    public function getUserInfo(): UserInterface
    {
        return $this->userInfo;
    }

    /**
     * @param \DateTimeInterface $deletedAt
     * @return Message
     */
    public function setDeletedAt(\DateTimeInterface $deletedAt)
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    /**
     * @return null|\DateTimeInterface
     */
    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function __toString(): string
    {
        return self::class . ':' . $this->id;
    }

    /**
     * @return string|null
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     *
     * @return Message
     */
    public function setIp(string $ip): Message
    {
        $this->ip = $ip;
        return $this;
    }
}

