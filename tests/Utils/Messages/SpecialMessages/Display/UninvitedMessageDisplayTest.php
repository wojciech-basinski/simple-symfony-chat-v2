<?php declare(strict_types = 1);

namespace Tests\Utils\Messages\SpecialMessages\Display;

use AppBundle\Utils\ChatConfig;
use AppBundle\Utils\Messages\SpecialMessages\Display\UninvitedMessageDisplay;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\Translation\TranslatorInterface;

class UninvitedMessageDisplayTest extends TestCase
{
    /**
     * @var MockObject & TranslatorInterface
     */
    private $translator;
    /**
     * @var MockObject & ChatConfig
     */
    private $config;
    /**
     * @var UninvitedMessageDisplay
     */
    private $uninvitedMessageDisplay;

    protected function setUp()
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->config = $this->createMock(ChatConfig::class);

        $this->uninvitedMessageDisplay = new UninvitedMessageDisplay($this->translator, $this->config);
    }

    public function testDisplay(): void
    {
        $this->config->method('getBotId')
            ->willReturn(1);
        $this->translator->method('getLocale')
            ->willReturn('en');
        $this->translator->method('trans')
            ->with(
                'chat.uninviteSent',
                ['chat.user' => 'user'],
                'chat',
                'en'
            )
            ->willReturn('some text');

        $this->assertEquals(
            [
                'showText' => 'some text',
                'userId' => 1
            ],
            $this->uninvitedMessageDisplay->display(['text', 'user'])
        );
    }
}
