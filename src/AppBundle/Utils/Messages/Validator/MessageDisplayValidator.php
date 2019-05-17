<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\Validator;

use AppBundle\Entity\User;
use AppBundle\Utils\Channel;

class MessageDisplayValidator
{
    /**
     * @var Channel
     */
    private $channel;

    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }
    /**
     * Checking if message can be displayed on chat, unset messages that cannot be displayed
     *
     * @param array $messages messages as array
     *
     * @param User $user
     *
     * @return array checked messages
     */
    public function checkIfMessagesCanBeDisplayed(array $messages, User $user): array
    {
        $count = count($messages);
        for ($i = 0; $i < $count; $i++) {
            $textSplitted = explode(' ', $messages[$i]['text']);
            if ($textSplitted[0] === '/delete') {
                unset($messages[$i]);
            }
            if (!$this->channel->checkIfUserCanBeOnThatChannel($user, $messages[$i]['channel'])) {
                unset($messages[$i]);
            }
        }

        return $messages;
    }
}