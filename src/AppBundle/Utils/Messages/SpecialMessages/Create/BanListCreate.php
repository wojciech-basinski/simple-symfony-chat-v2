<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\SpecialMessages\Create;

use AppBundle\Entity\User;
use AppBundle\Utils\ChatConfig;
use AppBundle\Utils\Messages\Database\AddMessageToDatabase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BanListCreate implements SpecialMessageAdd
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var AuthorizationCheckerInterface
     */
    private $auth;
    /**
     * @var AddMessageToDatabase
     */
    private $addMessageToDatabase;
    /**
     * @var ChatConfig
     */
    private $config;

    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $em,
        SessionInterface $session,
        AuthorizationCheckerInterface $auth,
        AddMessageToDatabase $addMessageToDatabase,
        ChatConfig $config
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->session = $session;
        $this->auth = $auth;
        $this->addMessageToDatabase = $addMessageToDatabase;
        $this->config = $config;
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
        if (!$this->auth->isGranted('ROLE_MODERATOR', $user)) {
            return $this->permissionDenied();
        }

        $this->addMessageToDatabase->addBotMessage(
            $this->getListBannedText(),
            $this->config->getUserPrivateMessageChannelId($user)
        );
        return true;
    }

    private function permissionDenied(): bool
    {
        $errorText = $this->translator->trans(
            'error.notPermittedToListBan',
            [],
            'chat',
            $this->translator->getLocale()
        );
        $this->session->set(
            'errorMessage',
            $errorText
        );
        return false;
    }

    private function getListBannedText(): string
    {
        $text = '/banlist';
        /** @var User[] $bannedUsers */
        $bannedUsers = $this->em->getRepository(User::class)->getBannedUsers();
        foreach ($bannedUsers as $user) {
            $text .= ' ' . $user->getUsername() .', ';
        }
        return \rtrim($text, ', ');
    }
}
