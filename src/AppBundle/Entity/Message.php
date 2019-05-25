<?php declare(strict_types = 1);

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
    private $deletedAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $ip;

    public function setUserInfo(User $userInfo): Message
    {
        $this->userInfo = $userInfo;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->userInfo->getUsername();
    }

    public function getUserAvatar(): string
    {
        return $this->userInfo->getAvatar();
    }

    public function getRole(): string
    {
        return $this->userInfo->getChatRoleAsText();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setUserId(int $userId): Message
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setDate(\DateTime $date): Message
    {
        $this->date = $date;

        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setText(string $text): Message
    {
        $this->text = $text;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setChannel(int $channel): Message
    {
        $this->channel = $channel;

        return $this;
    }

    public function getChannel(): int
    {
        return $this->channel;
    }

    public function getUserInfo(): UserInterface
    {
        return $this->userInfo;
    }

    public function setDeletedAt(\DateTimeInterface $deletedAt)
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function __toString(): string
    {
        return self::class . ':' . $this->id;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): Message
    {
        $this->ip = $ip;
        return $this;
    }
}
