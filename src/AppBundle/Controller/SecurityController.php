<?php declare(strict_types = 1);

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Utils\ChatConfig;
use AppBundle\Utils\UserOnline;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SecurityController extends Controller
{
    /**
     * @Route("/add/", name="add_online")
     *
     * Adds info about user to users online in database.
     *
     * @param UserOnline $userOnline
     *
     * @param SessionInterface $session
     *
     * @return Response
     * @throws \Exception
     */
    public function addOnlineUserAction(UserOnline $userOnline, SessionInterface $session): Response
    {
        $user = $this->getUser();
        $session->set('channel', 1);
        if ($userOnline->addUserOnline($user, 1)) {
            return $this->redirectToRoute('banned');
        }

        return $this->redirectToRoute('chat_index');
    }
}
