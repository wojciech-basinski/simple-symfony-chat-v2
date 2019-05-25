<?php declare(strict_types = 1);

namespace Tests\Utils\Messages\SpecialMessages\Display;

use AppBundle\Utils\Messages\SpecialMessages\Display\SentPrivateMessageDisplay;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\Translation\TranslatorInterface;

class SentPrivateMessageDisplayTest extends TestCase
{
    /**
     * @var MockObject & TranslatorInterface
     */
    private $translator;
    /**
     * @var SentPrivateMessageDisplay
     */
    private $sentPrivateMessageDisplay;

    protected function setUp()
    {
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->sentPrivateMessageDisplay = new SentPrivateMessageDisplay($this->translator);
    }

    public function testDisplay(): void
    {
        $this->translator->method('getLocale')
            ->willReturn('en');
        $this->translator->method('trans')
            ->with(
                'chat.privTo',
                ['chat.user' => 'user'],
                'chat',
                'en'
            )
            ->willReturn('some text');

        $this->assertEquals(
            [
                'showText' => 'some text text',
                'userId' => false
            ],
            $this->sentPrivateMessageDisplay->display(['text', 'user text'])
        );
    }
}
