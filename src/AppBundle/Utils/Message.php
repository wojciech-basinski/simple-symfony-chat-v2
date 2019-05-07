<?php declare(strict_types = 1);

namespace AppBundle\Utils;

use AppBundle\Entity\User;
use AppBundle\Utils\Messages\SpecialMessages;
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
     * @var Request
     */
    private $request;

    public function __construct(
        EntityManagerInterface $em,
        SessionInterface $session,
        ChatConfig $config,
        SpecialMessages $special,
        LoggerInterface $logger,
        RequestStack $request
    ) {
        $this->em = $em;
        $this->session = $session;
        $this->config = $config;
        $this->specialMessages = $special;
        $this->logger = $logger;
        $this->request = $request->getCurrentRequest();
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
    public function addMessageToDatabase(User $user, ?string $text): array
    {
        $channel = $this->session->get('channel');
        if (false === $this->validateMessage($user, $channel, $text)) {
            return $this->returnFail();
        }

        $special = $this->specialMessages->specialMessages($text, $user);

        if ($this->session->get('afk') === true && $special === ['userId' => false]) {
            $this->specialMessages->specialMessages('/afk', $user);
            $special['count'] = 1;
        }

        if ($special['userId'] === $this->config->getBotId()) {
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
            $id = ($special['message'] !== false) ? $special['message']->getId() : ($this->session->get('lastId') + $special['count']);
            $count = $special['count'];
        }

        //check if there was new messages between last message and send message
        if (($this->session->get('lastId') + $count) !== $id) {
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
                    if (isset($special['message'])) {
                        if ($message['id'] === $special['message']->getId()) {
                            unset($message);
                        }
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
    private function validateMessage(User $user, int $channel, ?string $text): bool
    {
        if ($text === null) {
            $this->session->set('errorMessage', 'Wiadomośc nie może być pusta');
            return false;
        }
        $text = strtolower(trim($text));
        if (strlen($text) <= 0) {
            $this->session->set('errorMessage', 'Wiadomośc nie może być pusta');
            return false;
        }
        if ($user->getId() <= 0) {
            $this->session->set('errorMessage', 'Nie możesz wysyłać wiadomości będąc nie zalogowanym');
            return false;
        }
        if (!array_key_exists($channel, $this->config->getChannels($user))) {
            $this->session->set('errorMessage', 'Nie możesz pisać na tym kanale');
            return false;
        }
        if (strpos($text, '(pm)') === 0) {
            $this->session->set('errorMessage', 'Wiadomośc nie może zaczynać się od (pm)');
            return false;
        }
        if (strpos($text, '(pw)') === 0) {
            $this->session->set('errorMessage', 'Wiadomośc nie może zaczynać się od (pw)');
            return false;
        }
        return true;
    }

    private function nl2br(string $string): string
    {
        return str_replace(["\r\n", "\r", "\n"], '<br />', $string);
    }

    private function returnFail(): array
    {
        return [
            'status' => 'false',
            'errorMessage' => $this->session->get('errorMessage')
        ];
    }
}
