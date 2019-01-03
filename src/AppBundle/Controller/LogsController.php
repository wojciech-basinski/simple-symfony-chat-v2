<?php
namespace AppBundle\Controller;

use AppBundle\Utils\Logs;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class LogsController extends Controller
{
    /**
     * @Route("/stalker", name="chat_logs_stalker")
     * @param AuthorizationCheckerInterface $auth
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function logsAction(AuthorizationCheckerInterface $auth)
    {
        if (!$auth->isGranted('ROLE_ADMIN')) {
            throw new UnauthorizedHttpException('You do not have permission to visit this page');
        }
        return $this->render('logs/index.html.twig');
    }

    /**
     * @Route("/stalker/view/{start}/{end}/{user}", name="chat_logs_view")
     * @param string $start
     * @param string $end
     * @param string $user
     * @param Logs $logs
     *
     * @param AuthorizationCheckerInterface $auth
     *
     * @return Response
     */
    public function logsViewAction(
        string $start,
        string $end,
        Logs $logs,
        AuthorizationCheckerInterface $auth,
        string $user = ''
    ) {
        if (!$auth->isGranted('ROLE_ADMIN')) {
            throw new UnauthorizedHttpException('You do not have permission to visit this page');
        }

        return $this->render('logs/logs.html.twig', [
            'messages' => $logs->getLogs($start, $end, $user)
        ]);
    }
}