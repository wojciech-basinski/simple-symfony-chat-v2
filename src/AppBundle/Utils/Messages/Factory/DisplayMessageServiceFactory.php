<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\Factory;

use AppBundle\Utils\Messages\SpecialMessages\Display\AfkMessageDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\InviteMessageDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\ReceivedPrivateMessageDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\ReturnFromAfkDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\SentPrivateMessageDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\RollMessageDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\SpecialMessageDisplay;
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

    public function __construct(
        RollMessageDisplay $rollDisplay,
        SentPrivateMessageDisplay $sentPrivateMessageDisplay,
        ReceivedPrivateMessageDisplay $receivedPrivateMessageDisplay,
        InviteMessageDisplay $inviteMessageDisplay,
        UninviteMessageDisplay $uninviteMessageDisplay,
        AfkMessageDisplay $afkMessageDisplay,
        ReturnFromAfkDisplay $returnFromAfkDisplay
    ) {
        $this->rollDisplay = $rollDisplay;
        $this->sentPrivateMessageDisplay = $sentPrivateMessageDisplay;
        $this->receivedPrivateMessageDisplay = $receivedPrivateMessageDisplay;
        $this->inviteMessageDisplay = $inviteMessageDisplay;
        $this->uninviteMessageDisplay = $uninviteMessageDisplay;
        $this->afkMessageDisplay = $afkMessageDisplay;
        $this->returnFromAfkDisplay = $returnFromAfkDisplay;
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
            case '/uninvite':
                return $this->uninviteMessageDisplay;
            case '/afk':
                return $this->afkMessageDisplay;
            case '/returnAfk':
                return $this->returnFromAfkDisplay;
            default:
                return null;
        }

    }
}