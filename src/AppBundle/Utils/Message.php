<?php

namespace AppBundle\Utils;

use AppBundle\Entity\User;
use AppBundle\Utils\SpecialMessages\SpecialMessages;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use AppBundle\Entity\Message as MessageEntity;

/**
 * Service to preparing messages from database to array or check if new message can be add to database
 *
 * Class Message
 * @package AppBundle\Utils
 */
class Message
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var ChatConfig
     */
    private $config;
    /**
     * @var SpecialMessages
     */
    private $specialMessages;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Channel
     */
    private $channel;
    /**
     * @var Request
     */
    private $request;

    public function __construct(
        EntityManagerInterface $em,
        SessionInterface $session,
        ChatConfig $config,
        SpecialMessages $special,
        LoggerInterface $logger,
        Channel $channel,
        RequestStack $request
    ) {
        $this->em = $em;
        $this->session = $session;
        $this->config = $config;
        $this->specialMessages = $special;
        $this->logger = $logger;
        $this->channel = $channel;
        $this->request = $request->getCurrentRequest();
    }

    /**
     * Gets messages from last 24h limited by chat limit, than set id of last message to session
     * than change messages from entitys to array
     *
     * @param User $user
     *
     * @return array Array of messages changed to array
     */
    public function getMessagesInIndex(User $user): array
    {
        $channel = $this->session->get('channel');
        $channelPrivateMessage = $this->config->getUserPrivateMessageChannelId($user);

        $messages = $this->em->getRepository('AppBundle:Message')
            ->getMessagesFromLastDay($channel, $channelPrivateMessage);

        $this->session->set(
            'lastId',
            $this->em->getRepository('AppBundle:Message')
                ->getIdFromLastMessage()
        );

        $this->changeMessagesToArray($messages);

        return $this->checkIfMessagesCanBeDisplayed($messages, $user);
    }

    /**
     * Gets messages from database from last id read from session, then set id of last message to session if any message exists,
     * than change messages from entitys to array and checking if messages can be displayed
     *
     * @param User $user
     *
     * @return array Array of messages changed to array
     */
    public function getMessagesFromLastId(User $user): array
    {
        $lastId = $this->session->get('lastId');
        $channel = $this->session->get('channel');
        //only when channel was changed
        if ($this->session->get('changedChannel', null)) {
            $this->session->remove('changedChannel');
            return $this->getMessagesAfterChangingChannel($channel, $user);
        }

        $messages = $this->em->getRepository('AppBundle:Message')
            ->getMessagesFromLastId(
                $lastId,
                $this->config->getPrivateMessageAdd(),
                $this->config->getUserPrivateMessageChannelId($user)
            );

        //if get new messages, update var lastId in session
        if (end($messages)) {
            $this->session->set('lastId', end($messages)->getId());
        }
        $this->changeMessagesToArray($messages);

        $messagesToDisplay = $this->checkIfMessagesCanBeDisplayed($messages, $user);
        usort($messagesToDisplay, function ($a, $b) {
            return $a <=> $b;
        });

        return $messagesToDisplay;
    }

    /**
     * Gets messages from last 24h from new channel, then set id of last message to session if any message exists,
     * than change messages from entitys to array and checking if messages can be displayed
     *
     * @param int $channel Channel's Id
     * @param User $user Current user
     *
     * @return array Array of messages changed to array
     */
    private function getMessagesAfterChangingChannel(int $channel, User $user): array
    {
        $messages = $this->em->getRepository('AppBundle:Message')
            ->getMessagesFromLastDay(
                $channel,
                $this->config->getUserPrivateMessageChannelId($user)
            );

        $lastId = $this->em->getRepository('AppBundle:Message')
            ->getIdFromLastMessage();
        $this->session->set('lastId', $lastId);

        $this->changeMessagesToArray($messages);
        $messagesToDisplay = $this->checkIfMessagesCanBeDisplayed($messages, $user);
        usort($messagesToDisplay, function ($a, $b) {
            return $a <=> $b;
        });

        return $messagesToDisplay;
    }

    /**
     * Validates messages and adds message to database, checks if there are new messages from last refresh,
     * save sent message's id to session as lastid
     *
     * @param User $user User instance, who is sending message
     * @param string $text Message's text
     *
     * @return array status of adding messages, and new messages from last refresh
     */
    public function addMessageToDatabase(User $user, string $text): array
    {
        $channel = $this->session->get('channel');
        if (false === $this->validateMessage($user, $channel, $text)) {
            return ['status' => 'false'];
        }

        $special = $this->specialMessages->specialMessages($text, $user);

        if ($special['userId'] == ChatConfig::getBotId()) {
            $originalUser = $user;
            $user = $this->em->find('AppBundle:User', ChatConfig::getBotId());
            $text = $special['text'];
        }

        $text = htmlentities($text);

        $text = $this->nl2br($text);

        if (!isset($special['message'])) {
            $message = new MessageEntity();
            $message->setUserInfo($user)
                ->setChannel($channel)
                ->setText($text)
                ->setDate(new \DateTime())
                ->setIp($this->request->server->get('REMOTE_ADDR'));
            $this->em->persist($message);
        }

        try {
            $this->em->flush();
        } catch (\Throwable $e) {
            return ['status' => 'false'];
        }
        if (isset($originalUser)) {
            $user = $originalUser;
        }
        if (!isset($special['message'])) {
            $id = $message->getId();
            $count = 1;
        } else {
            $id = ($special['message'] != false) ? $special['message']->getId() : ($this->session->get('lastId') + $special['count']);
            $count = $special['count'];
        }

        //check if there was new messages between last message and send message
        if (($this->session->get('lastId') + $count) != $id) {
            $messages = $this->em->getRepository(MessageEntity::class)
                ->getMessagesBetweenIds(
                    $this->session->get('lastId'),
                    $id,
                    $channel,
                    $this->config->getUserPrivateMessageChannelId($user)
                );
            if ($messages) {
                $this->changeMessagesToArray($messages);
                foreach ($messages as &$message) {
                    if ($message->getId() === $special['message']->getId()) {
                        unset($message);
                    }
                }
                $messagesToDisplay = $this->checkIfMessagesCanBeDisplayed($messages, $user);
                $id = end($messagesToDisplay)['id'];
            }
        }

        $this->session->set('lastId', $id);

        return [
            'id' => $id,
            'userName' => $special['userId'] ? 'BOT' : $user->getUsername(),
            'text' => $special['showText'] ?? $text,
            'avatar' => $special['userId'] ? 'https://phs-phsa.ml/bot_avatar.jpg' : $user->getAvatar(),
            'status' => 'true',
            'messages' => $messagesToDisplay ?? ''
        ];
    }

    /**
     * Deleting message from database
     *
     * @param int $id Message's id
     *
     * @param User $user User instance
     *
     * @return int status of deleting messages
     */
    public function deleteMessage(int $id, User $user): int
    {
        $channel = $this->session->get('channel');
        $message = $this->em->getRepository(MessageEntity::class)->find($id);

        $this->em->remove($message);
        $this->em->flush();
        
        $message = new MessageEntity();
            $message->setUserInfo($user)
            ->setChannel($channel)
            ->setText('/delete ' . $id)
            ->setDate(new \DateTime())
            ->setIp($this->request->server->get('REMOTE_ADDR'));

        $this->em->persist($message);
        $this->em->flush();

        return 1;
    }

    /**
     * Checking if message can be displayed on chat, unsetting messages that cannot be displayed
     *
     * @param array $messages messages as array
     *
     * @param User $user
     *
     * @return array checked messages
     */
    private function checkIfMessagesCanBeDisplayed(array $messages, User $user): array
    {
        $count = count($messages);
        for ($i = 0; $i < $count; $i++) {
            $textSplitted = explode(' ', $messages[$i]['text']);
            if ($textSplitted[0] == '/delete') {
                unset($messages[$i]);
            }
            if (!$this->channel->checkIfUserCanBeOnThatChannel($user, $messages[$i]['channel'])) {
                unset($messages[$i]);
            }
        }

        return $messages;
    }

    /**
     * Validating if message is valid (not empty etc.) or User and Channel exists
     *
     * @param User $user User instance
     *
     * @param int $channel Channel's id
     *
     * @param string $text message text
     *
     * @return bool status
     */
    private function validateMessage(User $user, int $channel, string $text): bool
    {
        $text = strtolower(trim($text));
        if ((strlen($text) <= 0)) {
            return false;
        }
        if ($user->getId() <= 0) {
            return false;
        }
        if (!array_key_exists($channel, $this->config->getChannels($user))) {
            return false;
        }
        if (strpos($text, '(pm)') === 0) {
            return false;
        }
        if (strpos($text, '(pw)') === 0) {
            return false;
        }
        return true;
    }

    /**
     * Changing mesages from entity to array
     *
     * @param $messages array[Message]|Message Messages to changed
     */
    private function changeMessagesToArray(array &$messages): void
    {
        foreach ($messages as &$message) {
            $message = $this->createArrayToJson($message);
        }
    }

    private function createArrayToJson(MessageEntity $message): array
    {
        $text = $this->specialMessages->specialMessagesDisplay($message->getText());

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
        if ($textSplitted[0] == '/delete') {
            $returnedArray['id'] = $textSplitted[1];
            $returnedArray['text'] = 'delete';
        }
        return $returnedArray;
    }

    private function nl2br(string $string): string
    {
        return str_replace(["\r\n", "\r", "\n"], '<br />', $string);
    }
}