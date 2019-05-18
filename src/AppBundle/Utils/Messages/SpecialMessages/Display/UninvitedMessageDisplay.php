<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\SpecialMessages\Display;

use AppBundle\Utils\ChatConfig;
use Symfony\Component\Translation\TranslatorInterface;

class UninvitedMessageDisplay implements SpecialMessageDisplay
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var ChatConfig
     */
    private $config;

    public function __construct(TranslatorInterface $translator, ChatConfig $config)
    {
        $this->translator = $translator;
        $this->config = $config;
    }

    /**
     * Display special message
     */
    public function display(array $textSplitted): array
    {
        $textSplitted = explode(' ', $textSplitted[1]);
        $text = $this->translator->trans(
            'chat.uninviteSent',
            [
                'chat.user' => $textSplitted[0]
            ],
            'chat',
            $this->translator->getLocale()
        );

        return [
            'showText' => $text,
            'userId' => $this->config->getBotId()
        ];
    }
}