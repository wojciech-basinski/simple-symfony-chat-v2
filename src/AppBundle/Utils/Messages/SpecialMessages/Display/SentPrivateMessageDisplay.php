<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\SpecialMessages\Display;

use Symfony\Component\Translation\TranslatorInterface;

class SentPrivateMessageDisplay implements SpecialMessageDisplay
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Display special message
     */
    public function display(string $text): array
    {
        $text = explode(' ', $text, 2);
        $textSplitted = explode(' ', $text[1], 2);
        $text = $this->translator->trans(
                'chat.privTo',
                ['chat.user' => $textSplitted[0]],
                'chat',
                $this->translator->getLocale()
            ) . ' ' . $textSplitted[1];

        return [
            'showText' => $text,
            'userId' => false
        ];
    }
}