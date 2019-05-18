<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\Transformers;

use AppBundle\Entity\User;
use AppBundle\Utils\Messages\Factory\AddMessageServiceFactory;

class SpecialMessageAddTransformer
{
    /**
     * @var AddMessageServiceFactory
     */
    private $messageServiceFactory;

    public function __construct(
        AddMessageServiceFactory $messageServiceFactory
    ) {
        $this->messageServiceFactory = $messageServiceFactory;
    }

    /**
     * @param string $text
     * @param User $user
     * @param int $channel
     *
     * @return null | bool
     */
    public function specialMessagesAdd(string $text, User $user, int $channel): ?bool
    {
        $addMessageService = $this->messageServiceFactory->getAddService($text);
        if ($addMessageService === null) {
            return null;
        }
        return $addMessageService->add($this->explodeText($text), $user, $channel);
    }

    private function explodeText(string $text): array
    {
        return \explode(' ', $text, 2);
    }
}
