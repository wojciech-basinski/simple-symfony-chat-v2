<?php declare(strict_types = 1);

namespace AppBundle\Entity;

use AppBundle\Utils\ChatConfig;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="UserOnline", mappedBy="userInfo")
     */
    protected $userOnline;

    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="userInfo")
     */
    protected $userMessage;

    /**
     * @var string
     * @ORM\Column(name="avatar", type="string")
     */
    protected $avatar = '';

    /**
     * @var null|\DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $banned;

    /**
     * @var null|string
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $banReason;

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->userMessage = new ArrayCollection();
    }

    public function setId(int $id)
    {
        if (ChatConfig::getPhpBB() || ChatConfig::getMyBB()) {
            $this->id = $id;
        }
    }

    /**
     * @return string User's role change to text (used as css class)
     */
    public function getChatRoleAsText(): string
    {
        $role = $this->getRoles();
        if ($this->getUsername() === 'demotywatorking') {
            return 'demotywatorking';
        }
        switch ($role[0]) {
            case 'ROLE_ADMIN':
                return 'administrator';
            case 'ROLE_MODERATOR':
                return 'moderator';
            case 'ROLE_SHINY_LIDER':
                return 'hunter-lider';
            case 'ROLE_SHINY_HUNTER':
                return 'shiny-hunter';
            case 'ROLE_ELDERS':
                return 'elder';
            case 'ROLE_FRIEND':
                return 'friend';
            default:
                return 'user';
        }
    }

    /**
     * Changes User's role, removes other roles
     *
     * @param string $role Role that User will have
     *
     * @return $this
     */
    public function changeRole($role): User
    {
        switch ($role) {
            case 'user':
                $this->removeRole('ROLE_ADMIN');
                $this->removeRole('ROLE_MODERATOR');
                break;
            case 'moderator':
                $this->removeRole('ROLE_ADMIN');
                $this->removeRole('ROLE_USER');
                $this->addRole('ROLE_MODERATOR');
                break;
            case 'administrator':
                $this->removeRole('ROLE_MODERATOR');
                $this->removeRole('ROLE_USER');
                $this->addRole('ROLE_ADMIN');
                break;
        }

        return $this;
    }

    public function getAvatar(): string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): User
    {
        $this->avatar = $avatar;
        return $this;
    }

    public function setBanned(?\DateTime $banned): User
    {
        $this->banned = $banned;
        return $this;
    }

    public function getBanned(): ?\DateTime
    {
        return $this->banned;
    }

    public function setBanReason(?string $banReason): User
    {
        $this->banReason = $banReason;
        return $this;
    }

    public function getBanReason(): ?string
    {
        return $this->banReason;
    }
}
