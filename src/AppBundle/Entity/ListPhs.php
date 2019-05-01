<?php declare(strict_types = 1);

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * List
 *
 * @ORM\Table(name="lista_PHS")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ListRepository")
 */
class ListPhs
{
    /**
     * @var int
     *
     * @ORM\Column(name="KLUCZ", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="ID_GRACZA", type="integer")
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="NICK_GRACZA", type="string", length=255)
     */
    private $username;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="DATA", type="date")
     */
    private $date;


    public function getId(): int
    {
        return $this->id;
    }

    public function setUsername(string $username): ListPhs
    {
        $this->username = $username;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): ListPhs
    {
        $this->userId = $userId;

        return $this;
    }
}
