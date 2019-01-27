<?php
namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Utils\ChatConfig;
use AppBundle\Utils\UserOnline;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends Controller
{
    /**
     * @Route("/add/", name="add_online")
     *
     * Adds info about user to users online in database.
     *
     * @param UserOnline $userOnline
     *
     * @return Response
     */
    public function addOnlineUserAction(UserOnline $userOnline): Response
    {
        $user = $this->getUser();
        $this->get('session')->set('channel', 1);
        if ($userOnline->addUserOnline($user, 1)) {
            return $this->redirectToRoute('banned');
        }

        return $this->redirectToRoute('chat_index');
    }
}