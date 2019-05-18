<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\Transformers;

use AppBundle\Entity\Message;

class MessageToArrayTransformer
{
    /**
     * @var SpecialMessageDisplayTransformer
     */
    private $specialMessageDisplayTransformer;

    public function __construct(SpecialMessageDisplayTransformer $specialMessageDisplayTransformer)
    {
        $this->specialMessageDisplayTransformer = $specialMessageDisplayTransformer;
    }

    /**
     * Changing mesages from entity to array
     *
     * @param $messages array[Message]|Message Messages to changed
     *
     * @return array
     */
    public function transformMessagesToArray(array $messages): array
    {
        $messagesArray = [];
        foreach ($messages as $message) {
            $messagesArray[] = $this->createArrayToJson($message);
        }
        return $messagesArray;
    }

    private function createArrayToJson(Message $message): array
    {
        $text = $this->specialMessageDisplayTransformer->specialMessagesDisplay($message->getText());

        $returnedArray = [
            'id' => $message->getId(),
            'user_id' => $message->getUserId(),
            'date' => $message->getDate(),
            'text' => $text['showText'] ?? $message->getText(),
            'channel' => $message->getChannel(),
            'username' => $message->getUsername(),
            'user_role' => $message->getRole(),
            'privateMessage' => $text['privateMessage'] ?? 0,
            'user_avatar' => $message->getUserAvatar()
        ];

        $textSplitted = explode(' ', $message->getText());
        if ($textSplitted[0] === '/delete') {
            $returnedArray['id'] = $textSplitted[1];
            $returnedArray['text'] = 'delete';
        }
        return $returnedArray;
    }
}
