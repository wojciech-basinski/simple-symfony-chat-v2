<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\SpecialMessages\Display;

use Symfony\Component\Translation\TranslatorInterface;

class ReceivedPrivateMessageDisplay implements SpecialMessageDisplay
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    public function display(string $text): array
    {
        $text = explode(' ', $text, 2);
        $text = $this->translator->trans(
            'chat.privFrom',
            [],
            'chat',
            $this->translator->getLocale()
            ) . ' ' . $text[1];

        return [
            'showText' => $text,
            'userId' => false,
            'privateMessage' => 1
        ];
    }
}