<?php
namespace AppBundle\Controller;

use AppBundle\Utils\Banned;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class BannedController extends Controller
{
    /**
     * @Route("/banned/{user}", name="banned")
     * @param Banned $banned
     * @param string $user
     * @return Response
     */
    public function bannedAction(Banned $banned, string $user)
    {
        $reason = $banned->getReason($user);
        $time = $banned->getTime($user);
        if ($time <= new \DateTime('now')) {
            $banned->removeBan($user);
            $this->addFlash('success', 'Ban został zdjęty, zaloguj się ponownie');
            return $this->redirectToRoute('fos_user_security_login');
        }
        $timeFormatted = $time->format('Y-m-d H:i:s');
        $this->addFlash('error', "Ban do: $timeFormatted<br /> powód: $reason");
        return $this->render('chat/banned.html.twig');
    }
}