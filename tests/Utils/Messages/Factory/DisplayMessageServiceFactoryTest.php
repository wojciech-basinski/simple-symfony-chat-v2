<?php declare(strict_types = 1);

namespace Tests\Utils\Messages\Factory;

use AppBundle\Utils\Messages\Factory\DisplayMessageServiceFactory;
use AppBundle\Utils\Messages\SpecialMessages\Display\AfkMessageDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\BanListDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\BannedUserDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\InvitedMessageDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\InviteMessageDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\ReceivedPrivateMessageDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\ReturnFromAfkDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\RollMessageDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\SentPrivateMessageDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\UnBanUserDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\UninvitedMessageDisplay;
use AppBundle\Utils\Messages\SpecialMessages\Display\UninviteMessageDisplay;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class DisplayMessageServiceFactoryTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $rollDisplay;
    /**
     * @var MockObject
     */
    private $sentPrivateMessageDisplay;
    /**
     * @var MockObject
     */
    private $receivedPrivateMessageDisplay;
    /**
     * @var MockObject
     */
    private $inviteMessageDisplay;
    /**
     * @var MockObject
     */
    private $uninviteMessageDisplay;
    /**
     * @var MockObject
     */
    private $afkMessageDisplay;
    /**
     * @var MockObject
     */
    private $returnFromAfkDisplay;
    /**
     * @var MockObject
     */
    private $banListDisplay;
    /**
     * @var MockObject
     */
    private $bannedUserDisplay;
    /**
     * @var MockObject
     */
    private $unBanUserDisplay;
    /**
     * @var MockObject
     */
    private $invitedMessageDisplay;
    /**
     * @var MockObject
     */
    private $uninvitedMessageDisplay;
    /**
     * @var DisplayMessageServiceFactory
     */
    private $displayMessageServiceFactory;

    protected function setUp()
    {
        parent::setUp();
        $this->rollDisplay = $this->createMock(RollMessageDisplay::class);
        $this->sentPrivateMessageDisplay = $this->createMock(SentPrivateMessageDisplay::class);
        $this->receivedPrivateMessageDisplay = $this->createMock(ReceivedPrivateMessageDisplay::class);
        $this->inviteMessageDisplay = $this->createMock(InviteMessageDisplay::class);
        $this->uninviteMessageDisplay = $this->createMock(UninviteMessageDisplay::class);
        $this->afkMessageDisplay = $this->createMock(AfkMessageDisplay::class);
        $this->returnFromAfkDisplay = $this->createMock(ReturnFromAfkDisplay::class);
        $this->banListDisplay = $this->createMock(BanListDisplay::class);
        $this->bannedUserDisplay = $this->createMock(BannedUserDisplay::class);
        $this->unBanUserDisplay = $this->createMock(UnBanUserDisplay::class);
        $this->invitedMessageDisplay = $this->createMock(InvitedMessageDisplay::class);
        $this->uninvitedMessageDisplay = $this->createMock(UninvitedMessageDisplay::class);

        $this->displayMessageServiceFactory = new DisplayMessageServiceFactory(
            $this->rollDisplay,
            $this->sentPrivateMessageDisplay,
            $this->receivedPrivateMessageDisplay,
            $this->inviteMessageDisplay,
            $this->uninviteMessageDisplay,
            $this->afkMessageDisplay,
            $this->returnFromAfkDisplay,
            $this->banListDisplay,
            $this->bannedUserDisplay,
            $this->unBanUserDisplay,
            $this->invitedMessageDisplay,
            $this->uninvitedMessageDisplay
        );
    }

    public function testGetDisplayService(): void
    {
        $this->assertEquals(
            $this->rollDisplay,
            $this->displayMessageServiceFactory->getDisplayService("/roll")
        );
        $this->assertEquals(
            $this->sentPrivateMessageDisplay,
            $this->displayMessageServiceFactory->getDisplayService("/privTo")
        );
        $this->assertEquals(
            $this->receivedPrivateMessageDisplay,
            $this->displayMessageServiceFactory->getDisplayService("/privMsg")
        );
        $this->assertEquals(
            $this->inviteMessageDisplay,
            $this->displayMessageServiceFactory->getDisplayService("/invite")
        );
        $this->assertEquals(
            $this->invitedMessageDisplay,
            $this->displayMessageServiceFactory->getDisplayService("/invited")
        );
        $this->assertEquals(
            $this->uninviteMessageDisplay,
            $this->displayMessageServiceFactory->getDisplayService("/uninvite")
        );
        $this->assertEquals(
            $this->uninvitedMessageDisplay,
            $this->displayMessageServiceFactory->getDisplayService("/uninvited")
        );
        $this->assertEquals(
            $this->afkMessageDisplay,
            $this->displayMessageServiceFactory->getDisplayService("/afk")
        );
        $this->assertEquals(
            $this->returnFromAfkDisplay,
            $this->displayMessageServiceFactory->getDisplayService("/returnAfk")
        );
        $this->assertEquals(
            $this->banListDisplay,
            $this->displayMessageServiceFactory->getDisplayService("/banlist")
        );
        $this->assertEquals(
            $this->bannedUserDisplay,
            $this->displayMessageServiceFactory->getDisplayService("/banned")
        );
        $this->assertEquals(
            $this->unBanUserDisplay,
            $this->displayMessageServiceFactory->getDisplayService("/unban")
        );
        $this->assertNull($this->displayMessageServiceFactory->getDisplayService('text with nothing special'));
    }
}