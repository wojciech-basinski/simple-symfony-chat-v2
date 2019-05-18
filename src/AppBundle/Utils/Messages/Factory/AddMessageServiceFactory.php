<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\Factory;

use AppBundle\Utils\Messages\SpecialMessages\Create\AfkMessageCreate;
use AppBundle\Utils\Messages\SpecialMessages\Create\BanListCreate;
use AppBundle\Utils\Messages\SpecialMessages\Create\BanUserCreate;
use AppBundle\Utils\Messages\SpecialMessages\Create\InviteMessageCreate;
use AppBundle\Utils\Messages\SpecialMessages\Create\PrivateMessageCreate;
use AppBundle\Utils\Messages\SpecialMessages\Create\RollMessageCreate;
use AppBundle\Utils\Messages\SpecialMessages\Create\SpecialMessageAdd;
use AppBundle\Utils\Messages\SpecialMessages\Create\UnBanUserCreate;
use AppBundle\Utils\Messages\SpecialMessages\Create\UnInviteMessageCreate;

class AddMessageServiceFactory
{
    /**
     * @var RollMessageCreate
     */
    private $rollMessageCreate;
    /**
     * @var AfkMessageCreate
     */
    private $afkMessageCreate;
    /**
     * @var BanListCreate
     */
    private $banListCreate;
    /**
     * @var BanUserCreate
     */
    private $banUserCreate;
    /**
     * @var UnBanUserCreate
     */
    private $unBanUserCreate;
    /**
     * @var InviteMessageCreate
     */
    private $inviteMessageCreate;
    /**
     * @var UnInviteMessageCreate
     */
    private $unInviteMessageCreate;
    /**
     * @var PrivateMessageCreate
     */
    private $privateMessageCreate;

    public function __construct(
        RollMessageCreate $rollMessageCreate,
        AfkMessageCreate $afkMessageCreate,
        BanListCreate $banListCreate,
        BanUserCreate $banUserCreate,
        UnBanUserCreate $unBanUserCreate,
        InviteMessageCreate $inviteMessageCreate,
        UnInviteMessageCreate $unInviteMessageCreate,
        PrivateMessageCreate $privateMessageCreate
    ) {
        $this->rollMessageCreate = $rollMessageCreate;
        $this->afkMessageCreate = $afkMessageCreate;
        $this->banListCreate = $banListCreate;
        $this->banUserCreate = $banUserCreate;
        $this->unBanUserCreate = $unBanUserCreate;
        $this->inviteMessageCreate = $inviteMessageCreate;
        $this->unInviteMessageCreate = $unInviteMessageCreate;
        $this->privateMessageCreate = $privateMessageCreate;
    }

    public function getAddService(string $text): ?SpecialMessageAdd
    {
        $textSplitted = explode(' ', $text, 2);

        switch (strtolower($textSplitted[0])) {
            case '/roll':
                return $this->rollMessageCreate;
            case '/zw':
            case '/afk':
            case '/jj':
                return $this->afkMessageCreate;
            case '/banlist':
                return $this->banListCreate;
            case '/ban':
                return $this->banUserCreate;
            case '/unban':
                return  $this->unBanUserCreate;
            case '/invite':
                return $this->inviteMessageCreate;
            case '/uninvite':
                return $this->unInviteMessageCreate;
            case '/priv':
            case '/msg':
                return $this->privateMessageCreate;
            default:
                return null;
        }
    }
}