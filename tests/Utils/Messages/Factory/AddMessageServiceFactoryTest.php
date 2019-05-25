<?php declare(strict_types = 1);

namespace Tests\Utils\Messages\Factory;

use AppBundle\Utils\Messages\Factory\AddMessageServiceFactory;
use AppBundle\Utils\Messages\SpecialMessages\Create\AfkMessageCreate;
use AppBundle\Utils\Messages\SpecialMessages\Create\BanListCreate;
use AppBundle\Utils\Messages\SpecialMessages\Create\BanUserCreate;
use AppBundle\Utils\Messages\SpecialMessages\Create\InviteMessageCreate;
use AppBundle\Utils\Messages\SpecialMessages\Create\PrivateMessageCreate;
use AppBundle\Utils\Messages\SpecialMessages\Create\RollMessageCreate;
use AppBundle\Utils\Messages\SpecialMessages\Create\UnBanUserCreate;
use AppBundle\Utils\Messages\SpecialMessages\Create\UnInviteMessageCreate;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class AddMessageServiceFactoryTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $rollMessageCreate;
    /**
     * @var MockObject
     */
    private $privateMessageCreate;
    /**
     * @var MockObject
     */
    private $afkMessageCreate;
    /**
     * @var MockObject
     */
    private $banListCreate;
    /**
     * @var MockObject
     */
    private $banUserCreate;
    /**
     * @var MockObject
     */
    private $unBanUserCreate;
    /**
     * @var MockObject
     */
    private $inviteMessageCreate;
    /**
     * @var MockObject
     */
    private $unInviteMessageCreate;
    /**
     * @var AddMessageServiceFactory
     */
    private $addMessageServiceFactory;

    protected function setUp()
    {
        parent::setUp();
        $this->rollMessageCreate = $this->createMock(RollMessageCreate::class);
        $this->afkMessageCreate = $this->createMock(AfkMessageCreate::class);
        $this->banListCreate = $this->createMock(BanListCreate::class);
        $this->banUserCreate = $this->createMock(BanUserCreate::class);
        $this->unBanUserCreate = $this->createMock(UnBanUserCreate::class);
        $this->inviteMessageCreate = $this->createMock(InviteMessageCreate::class);
        $this->unInviteMessageCreate = $this->createMock(UnInviteMessageCreate::class);
        $this->privateMessageCreate = $this->createMock(PrivateMessageCreate::class);

        $this->addMessageServiceFactory = new AddMessageServiceFactory(
            $this->rollMessageCreate,
            $this->afkMessageCreate,
            $this->banListCreate,
            $this->banUserCreate,
            $this->unBanUserCreate,
            $this->inviteMessageCreate,
            $this->unInviteMessageCreate,
            $this->privateMessageCreate
        );
    }

    public function testGetAddService(): void
    {
        $this->assertEquals(
            $this->rollMessageCreate,
            $this->addMessageServiceFactory->getAddService("/roll")
        );
        $this->assertEquals(
            $this->afkMessageCreate,
            $this->addMessageServiceFactory->getAddService("/zw")
        );
        $this->assertEquals(
            $this->afkMessageCreate,
            $this->addMessageServiceFactory->getAddService("/afk")
        );
        $this->assertEquals(
            $this->afkMessageCreate,
            $this->addMessageServiceFactory->getAddService("/jj")
        );
        $this->assertEquals(
            $this->banListCreate,
            $this->addMessageServiceFactory->getAddService("/banlist")
        );
        $this->assertEquals(
            $this->banUserCreate,
            $this->addMessageServiceFactory->getAddService("/ban")
        );
        $this->assertEquals(
            $this->unBanUserCreate,
            $this->addMessageServiceFactory->getAddService("/unban")
        );
        $this->assertEquals(
            $this->inviteMessageCreate,
            $this->addMessageServiceFactory->getAddService("/invite")
        );
        $this->assertEquals(
            $this->unInviteMessageCreate,
            $this->addMessageServiceFactory->getAddService("/uninvite")
        );
        $this->assertEquals(
            $this->privateMessageCreate,
            $this->addMessageServiceFactory->getAddService("/priv")
        );
        $this->assertEquals(
            $this->privateMessageCreate,
            $this->addMessageServiceFactory->getAddService("/msg")
        );
        $this->assertNull($this->addMessageServiceFactory->getAddService("text with nothing special"));
    }
}
