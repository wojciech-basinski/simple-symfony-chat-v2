<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\Factory;

use AppBundle\Utils\Messages\SpecialMessages\Display\AfkMessageDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\BanListDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\BannedUserDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\InvitedMessageDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\InviteMessageDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\ReceivedPrivateMessageDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\ReturnFromAfkDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\SentPrivateMessageDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\RollMessageDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\SpecialMessageDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\UnBanUserDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\UninvitedMessageDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\UninviteMessageDisplay;

class DisplayMessageServiceFactory
{
    /**
     * @var RollMessageDisplay
     */
    private $rollDisplay;
    /**
     * @var SentPrivateMessageDisplay
     */
    private $sentPrivateMessageDisplay;
    /**
     * @var ReceivedPrivateMessageDisplay
     */
    private $receivedPrivateMessageDisplay;
    /**
     * @var InviteMessageDisplay
     */
    private $inviteMessageDisplay;
    /**
     * @var UninviteMessageDisplay
     */
    private $uninviteMessageDisplay;
    /**
     * @var AfkMessageDisplay
     */
    private $afkMessageDisplay;
    /**
     * @var ReturnFromAfkDisplay
     */
    private $returnFromAfkDisplay;
    /**
     * @var BanListDisplay
     */
    private $banListDisplay;
    /**
     * @var BannedUserDisplay
     */
    private $bannedUserDisplay;
    /**
     * @var UnBanUserDisplay
     */
    private $unBanUserDisplay;
    /**
     * @var InvitedMessageDisplay
     */
    private $invitedMessageDisplay;
    /**
     * @var UninvitedMessageDisplay
     */
    private $uninvitedMessageDisplay;

    public function __construct(
        RollMessageDisplay $rollDisplay,
        SentPrivateMessageDisplay $sentPrivateMessageDisplay,
        ReceivedPrivateMessageDisplay $receivedPrivateMessageDisplay,
        InviteMessageDisplay $inviteMessageDisplay,
        UninviteMessageDisplay $uninviteMessageDisplay,
        AfkMessageDisplay $afkMessageDisplay,
        ReturnFromAfkDisplay $returnFromAfkDisplay,
        BanListDisplay $banListDisplay,
        BannedUserDisplay $bannedUserDisplay,
        UnBanUserDisplay $unBanUserDisplay,
        InvitedMessageDisplay $invitedMessageDisplay,
        UninvitedMessageDisplay $uninvitedMessageDisplay
    ) {
        $this->rollDisplay = $rollDisplay;
        $this->sentPrivateMessageDisplay = $sentPrivateMessageDisplay;
        $this->receivedPrivateMessageDisplay = $receivedPrivateMessageDisplay;
        $this->inviteMessageDisplay = $inviteMessageDisplay;
        $this->uninviteMessageDisplay = $uninviteMessageDisplay;
        $this->afkMessageDisplay = $afkMessageDisplay;
        $this->returnFromAfkDisplay = $returnFromAfkDisplay;
        $this->banListDisplay = $banListDisplay;
        $this->bannedUserDisplay = $bannedUserDisplay;
        $this->unBanUserDisplay = $unBanUserDisplay;
        $this->invitedMessageDisplay = $invitedMessageDisplay;
        $this->uninvitedMessageDisplay = $uninvitedMessageDisplay;
    }

    public function getDisplayService(string $text): ?SpecialMessageDisplay
    {
        $textSplitted = explode(' ', $text, 2);

        switch ($textSplitted[0]) {
            case '/roll':
                return $this->rollDisplay;
            case '/privTo':
                return $this->sentPrivateMessageDisplay;
            case '/privMsg':
                return $this->receivedPrivateMessageDisplay;
            case '/invite':
                return $this->inviteMessageDisplay;
            case '/invited':
                return $this->invitedMessageDisplay;
            case '/uninvite':
                return $this->uninviteMessageDisplay;
            case '/uninvited':
                return $this->uninvitedMessageDisplay;
            case '/afk':
                return $this->afkMessageDisplay;
            case '/returnAfk':
                return $this->returnFromAfkDisplay;
            case '/banlist':
                return $this->banListDisplay;
            case '/banned':
                return $this->bannedUserDisplay;
            case '/unban':
                return $this->unBanUserDisplay;
            default:
                return null;
        }
    }
}
