<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\Transformers;

use AppBundle\Utils\Messages\Factory\DisplayMessageServiceFactory;

class SpecialMessageDisplayTransformer
{
    /**
     * @var DisplayMessageServiceFactory
     */
    private $messageServiceFactory;

    public function __construct(DisplayMessageServiceFactory $messageServiceFactory)
    {
        $this->messageServiceFactory = $messageServiceFactory;
    }

    public function specialMessagesDisplay(string $text): array
    {
        $displayService = $this->messageServiceFactory->getDisplayService($text);
        if ($displayService === null) {
            return ['userId' => false];
        }
        return $displayService->display($this->explodeText($text));
    }

    private function explodeText(string $text): array
    {
        return \explode(' ', $text, 2);
    }
}