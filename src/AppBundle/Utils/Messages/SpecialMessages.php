<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages;

use AppBundle\Entity\Invite;
use AppBundle\Entity\Message;
use AppBundle\Entity\User;
use AppBundle\Utils\ChatConfig;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use AppBundle\Entity\UserOnline;

class SpecialMessages
{
    public function specialMessages(string $text, User $user): array
    {
        $textSplitted = explode(' ', $text, 2);

        switch ($textSplitted[0]) {
//            case '/remind':
        //todo
//                return $this->setReminder($textSplitted, $user);
            default:
                return ['userId' => false];
        }
    }

    private function setReminder(array $textSplitted, User $user): void//array
    {
    }
}
