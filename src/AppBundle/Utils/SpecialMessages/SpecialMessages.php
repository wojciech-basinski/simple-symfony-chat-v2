<?php

namespace AppBundle\Utils\SpecialMessages;

use AppBundle\Entity\Invite;
use AppBundle\Entity\Message;
use AppBundle\Entity\User;
use AppBundle\Utils\ChatConfig;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use AppBundle\Entity\UserOnline;

class SpecialMessages
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string user's locale
     */
    private $locale;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var ChatConfig
     */
    private $config;
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var AuthorizationCheckerInterface
     */
    private $auth;
    /**
     * @var Request
     */
    private $request;

    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $em,
        ChatConfig $config,
        SessionInterface $session,
        AuthorizationCheckerInterface $auth,
        RequestStack $request
    ) {
        $this->translator = $translator;
        $this->locale = $translator->getLocale();
        $this->em = $em;
        $this->config = $config;
        $this->session = $session;
        $this->auth = $auth;
        $this->request = $request->getCurrentRequest();
    }

    public function specialMessagesDisplay(string $text): array
    {
        $textSplitted = explode(' ', $text, 2);

        switch ($textSplitted[0]) {
            case '/roll':
                return $this->rollShow($textSplitted);
            case '/privTo':
                return $this->privToShow($textSplitted);
            case '/privMsg':
                return $this->privFromShow($textSplitted);
            case '/invite':
                return $this->inviteToShow($textSplitted);
            case '/uninvite':
                return $this->uninviteToShow($textSplitted);
            default:
                return ['userId' => false];
        }
    }

    public function specialMessages(string $text, User $user): array
    {
        $textSplitted = explode(' ', $text, 2);

        switch ($textSplitted[0]) {
            case '/roll':
                return $this->roll($textSplitted, $user);
            case '/priv':
            case '/msg':
                return $this->priv($textSplitted, $user);
            case '/invite':
                return $this->invite($textSplitted, $user);
            case '/uninvite':
                return $this->uninvite($textSplitted, $user);
            case '/ban':
                return $this->banUser($textSplitted, $user);
            case '/unban':
                return $this->unbanUser($textSplitted, $user);
            case '/banlist':
                return $this->banList($user);
            default:
                return ['userId' => false];
        }
    }

    private function roll(array $text, User $user): array
    {
        $dice = $this->createDice($text);

        $text = "/roll {$dice[0]}d{$dice[1]} {$user->getUsername()} ";
        $textSpecial = $user->getUsername() . ' ' .
            $this->translator->trans(
                'chat.roll',
                ['chat.dice' => "{$dice[0]}d{$dice[1]}"],
                'chat',
                $this->locale
            ) . ' ';
        for ($i = 0; $i < $dice[0]; $i++) {
            $result = $this->rollDice($dice[1]);
            $textSpecial .= $result . ', ';
            $text .= $result . ', ';
        }

        return [
            'showText' => rtrim($textSpecial, ', ') . '.',
            'text' => rtrim($text, ', ') . '.',
            'userId' => ChatConfig::getBotId()
        ];
    }

    private function rollDice(int $max): int
    {
        return mt_rand(1, $max);
    }

    private function rollShow(array $text): array
    {
        $textSplitted = explode(' ', $text[1], 3);
        $text = $textSplitted[1] . ' ' .
            $this->translator->trans(
                'chat.roll',
                ['chat.dice' => $textSplitted[0]],
                'chat', $this->locale
            ) . ' ' . $textSplitted[2];

        return [
            'showText' => $text,
            'userId' => ChatConfig::getBotId()
        ];
    }

    private function priv(array $text, User $user): array
    {
        if (!isset($text[1])) {
            $text = $text[0] . ' ' .
                $this->translator->trans('error.wrongUsername', [], 'chat', $this->locale);
            return ['userId' => ChatConfig::getBotId(), 'text' => $text, 'message' => false, 'count' => 0];
        }
        $textSplitted = explode(' ', $text[1], 2);
        $secondUser = $this->em->getRepository('AppBundle:User')->findOneBy(['username' => $textSplitted[0]]);
        if (!$secondUser) {
            $text = $text[0] . ' ' .
                $this->translator->trans('error.userNotFound', [], 'chat', $this->locale);
            return ['userId' => ChatConfig::getBotId(), 'text' => $text, 'message' => false, 'count' => 0];
        }

        $message1 = $this->insertPw($user, $secondUser, $textSplitted);
        $showText = $this->translator->trans(
            'chat.privTo',
            ['chat.user' => $secondUser->getUsername()],
            'chat',
            $this->locale
            ) . ' ' . $textSplitted[1];

        return ['userId' => false, 'message' => $message1, 'showText' => $showText, 'count' => 2];
    }

    private function insertPw(User $user, User $secondUser, array $textSplitted): Message
    {
        $message = new Message();
        $message->setUserId($secondUser->getId())
            ->setUserInfo($user)
            ->setChannel($this->config->getUserPrivateMessageChannelId($secondUser))
            ->setDate(new \DateTime())
            ->setText('/privMsg ' . $textSplitted[1])
            ->setIp($this->request->server->get('REMOTE_ADDR'));
        $this->em->persist($message);

        $message1 = new Message();
        $message1->setUserId($user->getId())
            ->setUserInfo($user)
            ->setChannel($this->config->getUserPrivateMessageChannelId($user))
            ->setDate(new \DateTime())
            ->setText('/privTo ' . $textSplitted[0] . ' ' . $textSplitted[1])
            ->setIp($this->request->server->get('REMOTE_ADDR'));
        $this->em->persist($message1);

        return $message1;
    }

    private function privToShow(array $text): array
    {
        $textSplitted = explode(' ', $text[1], 2);
        $text = $this->translator->trans(
            'chat.privTo',
            ['chat.user' => $textSplitted[0]],
            'chat',
            $this->locale
            ) . ' ' . $textSplitted[1];

        return [
            'showText' => $text,
            'userId' => false
        ];
    }

    private function privFromShow(array $text): array
    {
        $text = $this->translator->trans('chat.privFrom', [], 'chat', $this->locale) . ' ' . $text[1];

        return [
            'showText' => $text,
            'userId' => false,
            'privateMessage' => 1
        ];
    }

    private function invite(array $textSplitted, User $user): array
    {
        if (count($textSplitted) < 2) {
            $text = $textSplitted[0] . ' ' .
                $this->translator->trans('error.wrongUsername', [], 'chat', $this->locale);
            return ['userId' => ChatConfig::getBotId(), 'text' => $text, 'message' => false, 'count' => 0];
        }
        if ($this->session->get('channel') == 1) {
            $text = $textSplitted[0] . ' ' .
                $this->translator->trans('error.channelCant', [], 'chat', $this->locale);
            return ['userId' => ChatConfig::getBotId(), 'text' => $text, 'message' => false, 'count' => 0];
        }
        $userToInvite = $this->em->getRepository('AppBundle:User')->findOneBy(['username' => $textSplitted[1]]);
        if (!$userToInvite) {
            $text = $textSplitted[0] . ' ' . $this->translator->trans('error.userNotFound',
                    ['chat.nick' => $textSplitted[1]],
                    'chat',
                    $this->locale
                );
            return ['userId' => ChatConfig::getBotId(), 'text' => $text, 'message' => false, 'count' => 0];
        }

        if ($user->getId() === $userToInvite->getId()) {
            $text = $this->translator->trans('error.invitationSentYou', [], 'chat', $this->locale);
            return ['userId' => ChatConfig::getBotId(), 'message' => false, 'text' => $text, 'count' => 1];
        }

        $invite = $this->em->getRepository('AppBundle:Invite')->findOneBy([
            'channelId' => $this->session->get('channel'),
            'userId' => $userToInvite->getId()
        ]);
        if ($invite) {
            $text = $this->translator->trans(
                'error.invitationSent',
                ['chat.user' => $userToInvite->getUsername()],
                'chat',
                $this->locale
            );
            return ['userId' => ChatConfig::getBotId(), 'message' => false, 'text' => $text, 'count' => 1];
        }
        $invite = new Invite();
        $invite->setChannelId($this->session->get('channel'))
            ->setDate(new \DateTime())
            ->setInviterId($user->getId())
            ->setUserId($userToInvite->getId());

        $this->em->persist($invite);

        $this->insertInviteMessages($user, $userToInvite);

        $text = $this->translator->trans(
            'chat.invitationSent',
            ['chat.user' => $userToInvite->getUsername()],
            'chat',
            $this->locale
        );
        return ['userId' => ChatConfig::getBotId(), 'message' => false, 'text' => $text, 'count' => 1];
    }

    private function unInvite(array $textSplitted, User $user): array
    {
        if (count($textSplitted) < 2) {
            $text = $textSplitted[0] . ' ' .
                $this->translator->trans('error.wrongUsername', [], 'chat', $this->locale);
            return ['userId' => ChatConfig::getBotId(), 'text' => $text, 'message' => false, 'count' => 0];
        }
        if ($this->session->get('channel') == 1) {
            $text = $textSplitted[0] . ' ' .
                $this->translator->trans('error.channelCantUninvite', [], 'chat', $this->locale);
            return ['userId' => ChatConfig::getBotId(), 'text' => $text, 'message' => false, 'count' => 0];
        }
        $userToInvite = $this->em->getRepository('AppBundle:User')->findOneBy(['username' => $textSplitted[1]]);
        if (!$userToInvite) {
            $text = $textSplitted[0] . ' ' . $this->translator->trans('error.userNotFound',
                    ['chat.nick' => $textSplitted[1]],
                    'chat',
                    $this->locale
                );
            return ['userId' => ChatConfig::getBotId(), 'text' => $text, 'message' => false, 'count' => 0];
        }

        if ($user->getId() === $userToInvite->getId()) {
            $text = $this->translator->trans('error.uninviteYourself', [], 'chat', $this->locale);
            return ['userId' => ChatConfig::getBotId(), 'message' => false, 'text' => $text, 'count' => 1];
        }

        $invite = $this->em->getRepository('AppBundle:Invite')->findOneBy([
            'channelId' => $this->session->get('channel'),
            'userId' => $userToInvite->getId()
        ]);
        if (!$invite) {
            $text = $this->translator->trans(
                'error.invitationNotSent',
                ['chat.user' => $userToInvite->getUsername()],
                'chat',
                $this->locale
            );
            return ['userId' => ChatConfig::getBotId(), 'message' => false, 'text' => $text, 'count' => 1];
        }

        $this->em->remove($invite);

        $this->insertInviteMessages($user, $userToInvite, false);

        $text = $this->translator->trans(
            'chat.uninviteSent',
            ['chat.user' => $userToInvite->getUsername()],
            'chat',
            $this->locale
        );
        return ['userId' => ChatConfig::getBotId(), 'message' => false, 'text' => $text, 'count' => 1];
    }

    private function insertInviteMessages(User $user, User $userToInvite, bool $invite = true): void
    {
        $bot = $this->em->find('AppBundle:User', ChatConfig::getBotId());
        $channel = ($this->session->get('channel') == $this->config->getUserPrivateMessageChannelId($user)) ?
            $user->getUsername() : $this->config->getChannels($user)[$this->session->get('channel')];

        $text = $invite ? "/invite {$user->getUsername()} $channel" : "/uninvite {$user->getUsername()} $channel";

        $message = new Message();
        $message->setUserId($userToInvite->getId())
            ->setDate(new \DateTime())
            ->setChannel($this->config->getUserPrivateMessageChannelId($userToInvite))
            ->setUserInfo($bot)
            ->setText($text)
            ->setIp($this->request->server->get('REMOTE_ADDR'));
        $this->em->persist($message);
    }

    private function inviteToShow(array $text): array
    {
        $textSplitted = explode(' ', $text[1]);
        $text = $this->translator->trans(
            'chat.inviteToChannel',
            [
                'chat.user' => $textSplitted[0],
                'chat.channel' => $textSplitted[1]
            ],
            'chat',
            $this->locale
        );

        return [
            'showText' => $text,
            'userId' => ChatConfig::getBotId()
        ];
    }

    private function uninviteToShow(array $text): array
    {
        $textSplitted = explode(' ', $text[1]);
        $text = $this->translator->trans(
            'chat.uninviteToChannel',
            [
                'chat.channel' => $textSplitted[1]
            ],
            'chat',
            $this->locale
        );

        return [
            'showText' => $text,
            'userId' => ChatConfig::getBotId()
        ];
    }

    private function banUser(array $textSplitted, User $user): array
    {
        if (!$this->auth->isGranted('ROLE_MODERATOR', $user)) {
            $text = $this->translator->trans(
                'error.notPermittedToBan',
                [],
                'chat',
                $this->locale
            );
            return ['userId' => ChatConfig::getBotId(), 'message' => false, 'text' => $text, 'count' => 0];
        }
        $textParts = explode(' ', $textSplitted[1], 3);

        if (!count($textParts)) {
            $text = $this->translator->trans(
                'error.wrongUsername',
                [],
                'chat',
                $this->locale
            );
            return ['userId' => ChatConfig::getBotId(), 'message' => false, 'text' => $text, 'count' => 0];
        }
        $length = 60*60;
        if (count($textParts) > 1 && is_numeric($textParts[1])) {
            $length = $textParts[1] * 60;
        }
        /** @var User|null $userToBan */
        $userToBan = $this->em->getRepository(User::class)
            ->findOneByUsername($textParts[0]);
        if ($userToBan === null) {
            $text = $this->translator->trans(
                'error.userNotFound',
                ['chat.nick' => $textParts[0]],
                'chat',
                $this->locale
            );
            return ['userId' => ChatConfig::getBotId(), 'message' => false, 'text' => $text, 'count' => 0];
        }
        if (\in_array($userToBan->getChatRoleAsText(), ['administrator', 'demotywatorking'])) {
            $text = $this->translator->trans(
                'error.cantBanAdmin',
                [],
                'chat',
                $this->locale
            );
            return ['userId' => ChatConfig::getBotId(), 'message' => false, 'text' => $text, 'count' => 0];
        }
        if ($userToBan->getId() === $user->getId()) {
            $text = $this->translator->trans(
                'error.cantBanYourself',
                [],
                'chat',
                $this->locale
            );
            return ['userId' => ChatConfig::getBotId(), 'message' => false, 'text' => $text, 'count' => 0];
        }
        $reason = 'no details';
        if (count($textParts) > 2) {
            $reason = $textParts[2];
        }

        $userToBan->setBanReason($reason)
            ->setBanned((new \DateTime('now'))->modify("+ $length sec"));
        $this->em->persist($userToBan);
        $userOnline = $this->em->getRepository(UserOnline::class)
            ->findOneBy(['userId' => $userToBan->getId()]);
        if ($userOnline) {
            $this->em->remove($userOnline);
        }
        $this->em->flush();


        $text = $this->translator->trans(
            'chat.banned',
            ['chat.user' => $userToBan->getUsername()],
            'chat',
            $this->locale
        );
        return ['userId' => ChatConfig::getBotId(), 'message' => false, 'text' => $text, 'count' => 0];
    }

    private function banList(User $user): array
    {
        if (!$this->auth->isGranted('ROLE_MODERATOR', $user)) {
            $text = $this->translator->trans(
                'error.notPermittedToListBan',
                [],
                'chat',
                $this->locale
            );
            return ['userId' => ChatConfig::getBotId(), 'message' => false, 'text' => $text, 'count' => 0];
        }
        $bannedUsers = $this->em->getRepository(User::class)
            ->getBannedUsers();

        $text = $this->translator->trans(
            'chat.bannedUser',
            [],
            'chat',
            $this->locale
        );
        foreach ($bannedUsers as $user) {
            $text .= ' ' . $user->getUsername() .', ';
        }
        $text = rtrim($text, ', ');
        return ['userId' => ChatConfig::getBotId(), 'message' => false, 'text' => $text, 'count' => 0];
    }

    private function unbanUser(array $textSplitted, User $user): array
    {
        if (!$this->auth->isGranted('ROLE_MODERATOR', $user)) {
            $text = $this->translator->trans(
                'error.notPermittedToUnban',
                [],
                'chat',
                $this->locale
            );
            return ['userId' => ChatConfig::getBotId(), 'message' => false, 'text' => $text, 'count' => 0];
        }
        $textParts = explode(' ', $textSplitted[1]);

        if (!count($textParts)) {
            $text = $this->translator->trans(
                'error.wrongUsername',
                [],
                'chat',
                $this->locale
            );
            return ['userId' => ChatConfig::getBotId(), 'message' => false, 'text' => $text, 'count' => 0];
        }
        /** @var User|null $userToUnban */
        $userToUnban = $this->em->getRepository(User::class)
            ->findOneByUsername($textParts[0]);
        if ($userToUnban === null) {
            $text = $this->translator->trans(
                'error.userNotFound',
                ['chat.nick' => $textParts[0]],
                'chat',
                $this->locale
            );
            return ['userId' => ChatConfig::getBotId(), 'message' => false, 'text' => $text, 'count' => 0];
        }
        if ($userToUnban->getId() === $user->getId()) {
            $text = $this->translator->trans(
                'error.cantUnbanYourself',
                [],
                'chat',
                $this->locale
            );
            return ['userId' => ChatConfig::getBotId(), 'message' => false, 'text' => $text, 'count' => 0];
        }

        $userToUnban->setBanReason(null)
            ->setBanned(null);
        $this->em->persist($userToUnban);
        $this->em->flush();


        $text = $this->translator->trans(
            'chat.unbanned',
            ['chat.user' => $userToUnban->getUsername()],
            'chat',
            $this->locale
        );
        return ['userId' => ChatConfig::getBotId(), 'message' => false, 'text' => $text, 'count' => 0];
    }

    private function createDice(array $text): array
    {
        if (!isset($text[1])) {
            return [0 => 2, 1 => 6];
        }

        $dice = explode('d', $text[1]);

        if (count($dice) < 2) {
            return [0 => 2, 1 => 6];
        }
        if (!(is_numeric($dice[0])) || $dice[0] <= 0 || $dice[0] > 100) {
            $dice[0] = 2;
        }
        if (!(is_numeric($dice[1])) || $dice[1] <= 0 || $dice[1] > 100) {
            $dice[1] = 6;
        }
        return $dice;
    }
}