<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages;

use AppBundle\Entity\Message;
use AppBundle\Entity\User;
use AppBundle\Utils\Messages\Transformers\NewLineTransformer;
use AppBundle\Utils\Messages\Transformers\SpecialMessageAddTransformer;
use AppBundle\Utils\Messages\Validator\AddMessageValidator;
use AppBundle\Utils\Messages\Validator\UserAfkValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AddMessage
{
    /**
     * @var AddMessageValidator
     */
    private $addMessageValidator;
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var NewLineTransformer
     */
    private $newLineTransformer;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var UserAfkValidator
     */
    private $userAfkValidator;
    /**
     * @var SpecialMessageAddTransformer
     */
    private $specialMessageAddTransformer;
    /**
     * @var Request
     */
    private $request;

    public function __construct(
        AddMessageValidator $addMessageValidator,
        SessionInterface $session,
        NewLineTransformer $newLineTransformer,
        EntityManagerInterface $em,
        UserAfkValidator $userAfkValidator,
        SpecialMessageAddTransformer $specialMessageAddTransformer,
        RequestStack $requestStack
    ) {
        $this->addMessageValidator = $addMessageValidator;
        $this->session = $session;
        $this->newLineTransformer = $newLineTransformer;
        $this->em = $em;
        $this->userAfkValidator = $userAfkValidator;
        $this->specialMessageAddTransformer = $specialMessageAddTransformer;
        $this->request = $requestStack->getCurrentRequest();
    }

//    /**
//     * Validates messages and adds message to database, checks if there are new messages from last refresh,
//     * save sent message's id to session as lastid
//     *
//     * @param User $user User instance, who is sending message
//     * @param string $text Message's text
//     *
//     * @return array status of adding messages, and new messages from last refresh
//     */
    public function addMessageToDatabase(User $user, ?string $text, int $channel): array
    {
        if (false === $this->validateMessage($user, $text, $channel)) {
            return $this->returnFail();
        }

        if ($this->userAfkValidator->validateUserAfk($text)) {
            $this->specialMessageAddTransformer->specialMessagesAdd('/afk', $user, $channel);
        }

        $specialMessages = $this->specialMessageAddTransformer->specialMessagesAdd($text, $user, $channel);
        if ($specialMessages !== null) {
            return $specialMessages ? $this->returnSuccess() : $this->returnFail();
        }

        $this->addUserMessage($text, $user, $channel);

        return $this->returnSuccess();

//        return [
//            'id' => $id,
//            'userName' => $special['userId'] ? 'BOT' : $user->getUsername(),
//            'text' => $special['showText'] ?? $text,
//            'avatar' => $special['userId'] ? 'https://phs-phsa.ga/bot_avatar.jpg' : $user->getAvatar(),
//            'status' => 'true',
//            'messages' => $messagesToDisplay ?? ''
//        ];



//        if ($special === ['userId' => false] && $this->session->get('afk') === true) {
//            $this->specialMessages->specialMessages('/afk', $user);
//            $special['count'] = 1;
//        }
//
//        if ($special['userId'] === $this->config->getBotId()) {
//            $originalUser = $user;
//            $user = $this->em->find('AppBundle:User', $this->config->getBotId());
//            $text = $special['text'];
//        }
//
//        $text = htmlentities($text);//??
//
//        $text = $this->newLineTransformer->transformLine($text);
//
//        if (!isset($special['message'])) {
//            $message = new MessageEntity();
//            $message->setUserInfo($user)
//                ->setChannel($channel)
//                ->setText($text)
//                ->setDate(new \DateTime())
//                ->setIp($this->request->server->get('REMOTE_ADDR'));
//            $this->em->persist($message);
//        }
//
//        try {
//            $this->em->flush();
//        } catch (\Throwable $e) {
//            return ['status' => 'false'];
//        }
//        if (isset($originalUser)) {
//            $user = $originalUser;
//        }
//        if (!isset($special['message'])) {
//            $id = $message->getId();
//            $count = 1;
//        } else {
//            $id = ($special['message'] !== false) ? $special['message']->getId() : ($this->session->get('lastId') + $special['count']);
//            $count = $special['count'];
//        }
//
//        //check if there was new messages between last message and send message
//        if (($this->session->get('lastId') + $count) !== $id) {
//            $messages = $this->em->getRepository(MessageEntity::class)
//                ->getMessagesBetweenIds(
//                    $this->session->get('lastId'),
//                    $id,
//                    $channel,
//                    $this->config->getUserPrivateMessageChannelId($user)
//                );
//            if ($messages) {
//                $this->messageToArrayTransformer->transformMessagesToArray($messages);
//                foreach ($messages as &$message) {
//                    if (isset($special['message'])) {
//                        if ($message['id'] === $special['message']->getId()) {
//                            unset($message);
//                        }
//                    }
//                }
//                $messagesToDisplay = $this->messageDisplayValidator->checkIfMessagesCanBeDisplayed($messages, $user);
//                $id = end($messagesToDisplay)['id'];
//            }
//        }
//
//        $this->session->set('lastId', $id);
//
//        return [
//            'id' => $id,
//            'userName' => $special['userId'] ? 'BOT' : $user->getUsername(),
//            'text' => $special['showText'] ?? $text,
//            'avatar' => $special['userId'] ? 'https://phs-phsa.ga/bot_avatar.jpg' : $user->getAvatar(),
//            'status' => 'true',
//            'messages' => $messagesToDisplay ?? ''
//        ];
    }

    private function returnFail(): array
    {
        return [
            'status' => 'false',
            'errorMessage' => $this->session->get('errorMessage')
        ];
    }

    private function returnSuccess(): array
    {
        return [
            'status' => 'true',
        ];
    }

    private function validateMessage(User $user, ?string $text, int $channel): bool
    {
        return $this->addMessageValidator->validateMessage($user, $channel, $text);
    }

    private function addUserMessage(string $text, User $user, int $channel): void
    {
        $text = $this->newLineTransformer->transformLine(\htmlentities($text));
        $message = (new Message())
            ->setText($text)
            ->setUserInfo($user)
            ->setDate(new \DateTime())
            ->setChannel($channel)
            ->setIp($this->request->server->get('REMOTE_ADDR'));
        $this->em->persist($message);
        $this->em->flush();
    }
}
