<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\SpecialMessages\Display;

use AppBundle\Utils\ChatConfig;
use Symfony\Component\Translation\TranslatorInterface;

class BanListDisplay implements SpecialMessageDisplay
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var ChatConfig
     */
    private $config;

    public function __construct(TranslatorInterface $translator, ChatConfig  $config)
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
            'chat.bannedUser',
            [],
            'chat',
            $this->translator->getLocale()
        );

        return [
            'showText' => $text . ' ' . $textSplitted[1],
            'userId' => $this->config->getBotId()
        ];
    }
}
