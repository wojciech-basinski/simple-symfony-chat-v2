<?php
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


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set username.
     *
     * @param string $username
     *
     * @return List
     */
    public function setUsername($username): ListPhs
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return List
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId): ListPhs
    {
        $this->userId = $userId;

        return $this;
    }
}
