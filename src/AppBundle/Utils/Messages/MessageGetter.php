<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages;

use AppBundle\Entity\User;
use AppBundle\Utils\Channel;
use AppBundle\Utils\ChatConfig;
use AppBundle\Utils\Messages\Transformers\MessageToArrayTransformer;
use AppBundle\Utils\Messages\Validator\MessageDisplayValidator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MessageGetter
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
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var MessageToArrayTransformer
     */
    private $messageTransformer;
    /**
     * @var MessageDisplayValidator
     */
    private $messageDisplayValidator;

    public function __construct(
        EntityManagerInterface $em,
        SessionInterface $session,
        ChatConfig $config,
        LoggerInterface $logger,
        MessageToArrayTransformer $messageTransformer,
        MessageDisplayValidator $messageDisplayValidator
    ) {
        $this->em = $em;
        $this->session = $session;
        $this->config = $config;
        $this->logger = $logger;
        $this->messageTransformer = $messageTransformer;
        $this->messageDisplayValidator = $messageDisplayValidator;
    }

    /**
     * Gets messages from last 24h limited by chat limit, than set id of last message to session
     * than change messages from entities to array
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

        $messages = $this->messageTransformer->transformMessagesToArray($messages);

        return $this->messageDisplayValidator->checkIfMessagesCanBeDisplayed($messages, $user);
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
        //only when channel was changed
        if ($this->session->get('changedChannel', null)) {
            $this->session->remove('changedChannel');
            return $this->getMessagesAfterChangingChannel($user);
        }

        $messages = $this->em->getRepository('AppBundle:Message')
            ->getMessagesFromLastId(
                $lastId,
                $this->config->getPrivateMessageAdd(),
                $this->config->getUserPrivateMessageChannelId($user)
            );

        //if get new messages, update var lastId in session
        if (\end($messages)) {
            $this->session->set('lastId', \end($messages)->getId());
        }
        $messages = $this->messageTransformer->transformMessagesToArray($messages);

        $messagesToDisplay = $this->messageDisplayValidator->checkIfMessagesCanBeDisplayed($messages, $user);
        \usort($messagesToDisplay, static function ($a, $b) {
            return $a <=> $b;
        });

        return $messagesToDisplay;
    }

    /**
     * Gets messages from last 24h from new channel, then set id of last message to session if any message exists,
     * than change messages from entitys to array and checking if messages can be displayed
     *
     * @param User $user Current user
     *
     * @return array Array of messages changed to array
     */
    private function getMessagesAfterChangingChannel(User $user): array
    {
        $messages = $this->getMessagesInIndex($user);
        \usort($messages, static function ($a, $b) {
            return $a <=> $b;
        });

        return $messages;
    }
}