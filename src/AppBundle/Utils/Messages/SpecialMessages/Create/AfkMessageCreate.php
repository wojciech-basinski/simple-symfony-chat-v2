<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\SpecialMessages\Create;

use AppBundle\Entity\User;
use AppBundle\Entity\UserOnline;
use AppBundle\Utils\Cache\GetBotUserFromCache;
use AppBundle\Utils\Messages\Database\AddMessageToDatabase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AfkMessageCreate implements SpecialMessageAdd
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var AddMessageToDatabase
     */
    private $addMessageToDatabase;

    public function __construct(
        EntityManagerInterface $em,
        SessionInterface $session,
        AddMessageToDatabase $addMessageToDatabase
    ) {
        $this->em = $em;
        $this->session = $session;
        $this->addMessageToDatabase = $addMessageToDatabase;
    }
    /**
     * Add special message
     *
     * @param array $text
     * @param User $user
     * @param int $channel
     *
     * @return bool
     */
    public function add(array $text, User $user, int $channel): bool
    {
        return $this->afk($text, $user, $channel);
    }

    private function afk(array $text, User $user, int $channel): bool
    {
        /** @var UserOnline|null $userOnline */
        $userOnline = $this->em->getRepository(UserOnline::class)->findOneBy(['userId' => $user->getId()]);
        if ($userOnline === null) {
            $this->session->set(
                'errorMessage',
                'Error'
            );
            return false;
        }

        if ($userOnline->getAfk()) {
            return $this->removeAfk($text, $user, $userOnline, $channel);
        }

        $userOnline->setAfk(true);

        if (!isset($text[1])) {
            $this->session->set('afk', true);
            $this->addMessageToDatabase->addBotMessage(
                $this->createAfkText($user),
                $channel
            );
        }

        $this->updateUserOnline($userOnline);
        return true;
    }

    private function removeAfk(array $text, User $user, UserOnline $userOnline, int $channel): bool
    {
        $userOnline->setAfk(false);

        if (!isset($text[1])) {
            $this->session->set('afk', false);
            $this->addMessageToDatabase->addBotMessage(
                $this->createReturnFromAfkText($user),
                $channel
            );
        }

        $this->updateUserOnline($userOnline);
        return true;
    }

    private function createAfkText(User $user): string
    {
        return '/afk '.$user->getUsername();
    }

    private function createReturnFromAfkText(User $user): string
    {
        return '/returnAfk '.$user->getUsername();
    }

    private function updateUserOnline(UserOnline $userOnline): void
    {
        $this->em->persist($userOnline);
        $this->em->flush();
    }
}
