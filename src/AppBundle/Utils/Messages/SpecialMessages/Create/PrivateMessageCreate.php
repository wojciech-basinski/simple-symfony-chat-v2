<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\SpecialMessages\Create;

use AppBundle\Entity\User;
use AppBundle\Utils\ChatConfig;
use AppBundle\Utils\Messages\Database\AddMessageToDatabase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;

class PrivateMessageCreate implements SpecialMessageAdd
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var AddMessageToDatabase
     */
    private $addMessageToDatabase;
    /**
     * @var ChatConfig
     */
    private $config;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        TranslatorInterface $translator,
        SessionInterface $session,
        AddMessageToDatabase $addMessageToDatabase,
        ChatConfig $config,
        EntityManagerInterface $em
    ) {
        $this->translator = $translator;
        $this->session = $session;
        $this->addMessageToDatabase = $addMessageToDatabase;
        $this->config = $config;
        $this->em = $em;
    }
    /**
     * Add special message
     *
     * @param array $text
     * @param User $user
     * @param int $channel
     *
     * @return bool
     */
    public function add(array $text, User $user, int $channel): bool
    {
        if (!isset($text[1])) {
            return $this->wrongUsernameError();
        }
        $textSplitted = explode(' ', $text[1], 2);
        /** @var User|null $secondUser */
        $secondUser = $this->em->getRepository(User::class)->findOneBy(['username' => $textSplitted[0]]);
        return $this->insertPrivMessage($user, $secondUser, $textSplitted);
    }

    private function insertPrivMessage(User $user, ?User $secondUser, array $textSplitted): bool
    {
        if ($secondUser === null) {
            return $this->wrongUsernameError();
        }

        if (!isset($textSplitted[1])) {
            return $this->wrongMessageText();
        }

        $this->addMessageToDatabase->addMessage(
            '/privMsg ' . $textSplitted[1],
            $this->config->getUserPrivateMessageChannelId($secondUser),
            $user
        );
        $this->addMessageToDatabase->addMessage(
            '/privTo ' . $textSplitted[0] . ' ' . $textSplitted[1],
            $this->config->getUserPrivateMessageChannelId($user),
            $user
        );

        return true;
    }

    private function wrongMessageText(): bool
    {
        return $this->returnError('error.emptyPm');
    }

    private function wrongUsernameError(): bool
    {
        return $this->returnError('error.wrongUsername');
    }

    private function returnError(string $errorId, array $parameters = []): bool
    {
        $errorText = $this->translator->trans(
            $errorId,
            $parameters,
            'chat',
            $this->translator->getLocale()
        );
        $this->session->set(
            'errorMessage',
            $errorText
        );
        return false;
    }
}
