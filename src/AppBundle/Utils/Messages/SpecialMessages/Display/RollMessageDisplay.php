<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\SpecialMessages\Display;

use AppBundle\Utils\ChatConfig;
use Symfony\Component\Translation\TranslatorInterface;

class RollMessageDisplay implements SpecialMessageDisplay
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

    public function display(string $text): array
    {
        $text = explode(' ', $text, 2);
        $textSplitted = explode(' ', $text[1], 3);
        $text = $textSplitted[1] . ' ' .
            $this->translator->trans(
                'chat.roll',
                ['chat.dice' => $textSplitted[0]],
                'chat', $this->translator->getLocale()
            ) . ' ' . $textSplitted[2];

        return [
            'showText' => $text,
            'userId' => $this->config->getBotId()
        ];
    }
}