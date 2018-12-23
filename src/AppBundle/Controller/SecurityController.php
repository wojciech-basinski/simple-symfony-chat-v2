<?php
namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Utils\ChatConfig;
use AppBundle\Utils\UserOnline;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    /**
     * @Route("/add/", name="add_online")
     *
     * Adds info about user to users online in database.
     *
     * @param UserOnline $userOnline
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addOnlineUserAction(UserOnline $userOnline)
    {
        $user = $this->getUser();
        $this->get('session')->set('channel', 1);
        $userOnline->addUserOnline($user, 1);

        return $this->redirectToRoute('chat_index');
    }

    private function setUsersRoles(User &$user, array $value2)
    {
        switch ($value2[0]['group_id']) {
            case 5:
                $user->setRoles(['ROLE_ADMIN']);
                break;
            case 4:
                $user->setRoles(['ROLE_MODERATOR']);
                break;
            case 11:
                $user->setRoles(['ROLE_ELDERS']);
                break;
            case 12:
                $user->setRoles(['ROLE_SHINY_HUNTER']);
                break;
            case 13:
                $user->setRoles(['ROLE_FRIEND']);
                break;
            case 27:
                $user->setRoles(['ROLE_SHINY_LIDER']);
                break;
            default:
                $user->setRoles(['ROLE_USER']);
        }
    }
}