<?php declare(strict_types = 1);

namespace AppBundle\Utils;

use AppBundle\Entity\Invite;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ChatConfig
{
    /**
     * @var int time in second when user is logout from chat when he is inactivity
     */
    private const INACTIVE_TIME = 180;

    /**
     * @var array array of channels
     * DO NOT CHANGE FIRST CHANNEL
     */
    private const DEFAULT_CHANNELS = [
        1 => 'Default',
        7 => 'Pokemon Go'
    ];

    /**
     * @var int Login by MyBB forum user
     */
    private const MYBB = 0;

    /**
     * @var int Login by phpBB forum user
     */
    private const PHPBB = 1;

    /**
     * @var int moderator channel id
     */
    private const MODERATOR_CHANNEL_ID = 3;

    /**
     * @var int admin channel id
     */
    private const ADMIN_CHANNEL_ID = 4;

    /**
     * @var int shiny channel id
     */
    private const SHINY_CHANNEL_ID = 5;

    /**
     * @var int elders channel id
     */
    private const ELDERS_CHANNEL_ID = 6;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $auth;

    /**
     * @var int Bot Id
     */
    private const BOT_ID = 1;
    /**
     * @var int seconds in cooldown for roll for users
     */
    private const ROLL_COOL_DOWN = 30;

    /**
     * @var int added to private channel id
     */
    private const PRIVATE_CHANNEL_ADD = 1000000;

    /**
     * @var int added to private message channel id
     */
    private const PRIVATE_MESSAGE_ADD = 500000;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var null|array
     */
    private $invitations;

    public function __construct(
        AuthorizationCheckerInterface $auth,
        EntityManagerInterface $em
    ) {
        $this->auth = $auth;
        $this->em = $em;
    }

    /**
     * @param User $user
     *
     * @return array Array of channels
     */
    public function getChannels(User $user): array
    {
        return self::DEFAULT_CHANNELS +
            $this->specialChannels() +
            $this->getUserPrivateChannel($user) +
            $this->getChannelsFromInvitations($user);
    }

    public function getDefaultChannels(): array
    {
        return self::DEFAULT_CHANNELS;
    }

    public function getBotId(): int
    {
        return self::BOT_ID;
    }

    public static function getMyBB(): int
    {
        return self::MYBB;
    }

    public static function getPhpBB(): int
    {
        return self::PHPBB;
    }

    public function getInactiveTime(): int
    {
        return self::INACTIVE_TIME;
    }

    public function getUserPrivateChannel(User $user): array
    {
        $channelId = self::PRIVATE_CHANNEL_ADD + $user->getId();
        return [
            $channelId => 'Private'
        ];
    }

    public function getUserPrivateChannelId(User $user): int
    {
        return self::PRIVATE_CHANNEL_ADD + $user->getId();
    }

    public function getPrivateMessageAdd(): int
    {
        return self::PRIVATE_MESSAGE_ADD;
    }

    public function getUserPrivateMessageChannelId(User $user): int
    {
        return self::PRIVATE_MESSAGE_ADD + $user->getId();
    }

    public function getRollCoolDown(): int
    {
        return self::ROLL_COOL_DOWN;
    }

    private function specialChannels(): array
    {
        $array = [];
        if ($this->auth->isGranted('ROLE_ADMIN')) {
            $array[self::ADMIN_CHANNEL_ID] = $this->getChannelName(self::ADMIN_CHANNEL_ID);
        }
        if ($this->auth->isGranted('ROLE_MODERATOR')) {
            $array[self::MODERATOR_CHANNEL_ID] = $this->getChannelName(self::MODERATOR_CHANNEL_ID);
        }
        $array[self::SHINY_CHANNEL_ID] = $this->getChannelName(self::SHINY_CHANNEL_ID);
        if ($this->auth->isGranted('ROLE_ELDERS')) {
            $array[self::ELDERS_CHANNEL_ID] = $this->getChannelName(self::ELDERS_CHANNEL_ID);
        }
        return $array;
    }

    private function getChannelsFromInvitations(User $user): array
    {
        if ($this->invitations !== null) {
            return $this->invitations;
        }
        /** @var Invite[] $invitations */
        $invitations = $this->em->getRepository(Invite::class)->findBy([
            'userId' => $user->getId()
        ]);
        if (!$invitations) {
            $this->invitations = [];
            return [];
        }

        $return = [];
        foreach ($invitations as $invitation) {
            $channelId = $invitation->getChannelId();
            $return[$channelId] = $this->getChannelName($channelId);
        }
        $this->invitations = $return;
        return $return;
    }

    private function getChannelName(int $id): string
    {
        switch ($id) {
            case self::ADMIN_CHANNEL_ID:
                return 'Admin';
            case self::MODERATOR_CHANNEL_ID:
                return 'Moderator';
            case self::SHINY_CHANNEL_ID:
                return 'Shiny';
            case self::ELDERS_CHANNEL_ID:
                return 'Elders';
            default:
                return $this->getUserPrivateChannelName($id);
        }
    }

    private function getUserPrivateChannelName(int $id): string
    {
        $id -= self::PRIVATE_CHANNEL_ADD;
        /** @var User|null $user */
        $user = $this->em->find('AppBundle:User', $id);
        if ($user === null) {
            return '';
        }
        return $user->getUsername();
    }
}
