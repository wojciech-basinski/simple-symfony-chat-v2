<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\SpecialMessages\Display;

use AppBundle\Utils\ChatConfig;
use Symfony\Component\Translation\TranslatorInterface;

class ReturnFromAfkDisplay implements SpecialMessageDisplay
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
        $text = $this->translator->trans(
            'chat.returnFromAfk',
            ['chat.user' => $textSplitted[1]],
            'chat',
            $this->translator->getLocale()
        );

        return [
            'showText' => $text,
            'userId' => $this->config->getBotId()
        ];
    }
}
