<?php declare(strict_types = 1);

namespace Tests\Utils\Messages\Transformers;

use AppBundle\Entity\Message;
use AppBundle\Entity\User;
use AppBundle\Utils\Messages\Transformers\MessageToArrayTransformer;
use AppBundle\Utils\Messages\Transformers\SpecialMessageDisplayTransformer;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class MessageToArrayTransformerTest extends TestCase
{
    /**
     * @var MockObject & SpecialMessageDisplayTransformer
     */
    private $specialMessageDisplayTransformer;
    /**
     * @var MessageToArrayTransformer
     */
    private $messageToArrayTransformer;

    protected function setUp()
    {
        parent::setUp();
        $this->specialMessageDisplayTransformer = $this->createMock(SpecialMessageDisplayTransformer::class);
        $this->messageToArrayTransformer = new MessageToArrayTransformer($this->specialMessageDisplayTransformer);
    }

    public function testTransformMessagesToArray(): void
    {
        $messages = $this->createMessages();

        $this->assertEquals(
            [
                0 => [
                    'id' => null,
                    'user_id' => 1,
                    'date' => DateTime::createFromFormat('Y-m-d H:i:s', '2019-01-01 12:00:00'),
                    'text' => 'some text',
                    'channel' => 1,
                    'username' => 'username',
                    'user_role' => 'moderator',
                    'privateMessage' => 0,
                    'user_avatar' => 'some avatar'
                ],
                1 => [
                    'id' => null,
                    'user_id' => 2,
                    'date' => DateTime::createFromFormat('Y-m-d H:i:s', '2019-01-01 12:01:00'),
                    'text' => 'another text',
                    'channel' => 666,
                    'username' => 'username',
                    'user_role' => 'moderator',
                    'privateMessage' => 0,
                    'user_avatar' => 'some avatar'
                ],
                2 => [
                    'id' => 50,
                    'user_id' => 3,
                    'date' => DateTime::createFromFormat('Y-m-d H:i:s', '2019-01-02 12:01:00'),
                    'text' => 'delete',
                    'channel' => 6,
                    'username' => 'username',
                    'user_role' => 'moderator',
                    'privateMessage' => 0,
                    'user_avatar' => 'some avatar'
                ]
            ],
            $this->messageToArrayTransformer->transformMessagesToArray($messages)
        );
    }

    private function createMessages(): array
    {
        $user = (new User())
            ->setUsername('username')
            ->setRoles(['ROLE_MODERATOR'])
            ->setAvatar('some avatar');
        $message1 = (new Message())
            ->setChannel(1)
            ->setDate(DateTime::createFromFormat('Y-m-d H:i:s', '2019-01-01 12:00:00'))
            ->setText('some text')
            ->setUserInfo($user)
            ->setUserId(1);
        $message2 = (new Message())
            ->setChannel(666)
            ->setDate(DateTime::createFromFormat('Y-m-d H:i:s', '2019-01-01 12:01:00'))
            ->setText('another text')
            ->setUserInfo($user)
            ->setUserId(2);
        $message3 = (new Message())
            ->setChannel(6)
            ->setDate(DateTime::createFromFormat('Y-m-d H:i:s', '2019-01-02 12:01:00'))
            ->setText('/delete 50')
            ->setUserInfo($user)
            ->setUserId(3);
        return [$message1, $message2, $message3];
    }
}
