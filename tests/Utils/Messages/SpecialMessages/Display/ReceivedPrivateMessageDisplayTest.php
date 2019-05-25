<?php declare(strict_types = 1);

namespace Tests\Utils\Messages\SpecialMessages\Display;

use AppBundle\Utils\Messages\SpecialMessages\Display\ReceivedPrivateMessageDisplay;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\Translation\TranslatorInterface;

class ReceivedPrivateMessageDisplayTest extends TestCase
{
    /**
     * @var MockObject & TranslatorInterface
     */
    private $translator;
    /**
     * @var ReceivedPrivateMessageDisplay
     */
    private $receivedPrivateMessageDisplay;

    protected function setUp()
    {
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->receivedPrivateMessageDisplay = new ReceivedPrivateMessageDisplay($this->translator);
    }

    public function testDisplay(): void
    {
        $this->translator->method('getLocale')
            ->willReturn('en');
        $this->translator->method('trans')
            ->with(
                'chat.privFrom',
                [],
                'chat',
                'en'
            )
            ->willReturn('some text');

        $this->assertEquals(
            [
                'showText' => 'some text user',
                'userId' => false,
                'privateMessage' => 1
            ],
            $this->receivedPrivateMessageDisplay->display(['text', 'user'])
        );
    }
}