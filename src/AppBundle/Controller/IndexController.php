<?php declare(strict_types = 1);

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class IndexController extends Controller
{
    /**
     * @Route("/", name="homepage")
     *
     * Generates homepage, redirect to chat if user is logged in
     *
     * @param AuthorizationCheckerInterface $auth
     *
     * @return Response
     */
    public function indexAction(AuthorizationCheckerInterface $auth): Response
    {
        if ($auth->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('chat_index');
        }

        return $this->render('index/index.html.twig');
    }
}
