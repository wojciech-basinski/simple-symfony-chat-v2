<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\SpecialMessages\Create;

use AppBundle\Entity\User;
use AppBundle\Utils\ChatConfig;
use AppBundle\Utils\Messages\Database\AddMessageToDatabase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class UnBanUserCreate implements SpecialMessageAdd
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $auth;
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var AddMessageToDatabase
     */
    private $addMessageToDatabase;
    /**
     * @var ChatConfig
     */
    private $config;

    public function __construct(
        AuthorizationCheckerInterface $auth,
        SessionInterface $session,
        TranslatorInterface $translator,
        EntityManagerInterface $em,
        AddMessageToDatabase $addMessageToDatabase,
        ChatConfig $config
    ) {
        $this->auth = $auth;
        $this->session = $session;
        $this->translator = $translator;
        $this->em = $em;
        $this->addMessageToDatabase = $addMessageToDatabase;
        $this->config = $config;
    }
    /**
     * Add special message
     *
     * @param array $textSplitted
     * @param User $user
     * @param int $channel
     *
     * @return bool
     */
    public function add(array $textSplitted, User $user, int $channel): bool
    {
        if (!$this->auth->isGranted('ROLE_MODERATOR', $user)) {
            return $this->permissionDenied();
        }

        $textParts = explode(' ', $textSplitted[1]);

        if (!count($textParts)) {
            return $this->wrongUsernameError();
        }
        $userToUnban = $this->em->getRepository(User::class)
            ->findOneByUsername($textParts[0]);

        return $this->unbanUser($userToUnban, $user, $textParts);
    }

    private function unbanUser(?User $userToUnban, User $user, array $textParts): bool
    {
        if ($userToUnban === null) {
            return $this->userNotFoundError($textParts[0]);
        }
        if ($userToUnban->getId() === $user->getId()) {
            return $this->cantUnBanYourselfError();
        }

        $userToUnban->setBanReason(null)
            ->setBanned(null);
        $this->em->persist($userToUnban);
        $this->em->flush();

        $this->addMessageToDatabase->addBotMessage(
            '/unban ' . $userToUnban->getUsername(),
            $this->config->getUserPrivateMessageChannelId($user)
        );
        return true;
    }

    private function cantUnBanYourselfError(): bool
    {
        return $this->returnError('error.cantUnBanYourself');
    }

    private function permissionDenied(): bool
    {
        return $this->returnError('error.notPermittedToUnban');
    }

    private function wrongUsernameError(): bool
    {
        return $this->returnError('error.wrongUsername');
    }

    private function userNotFoundError(array $textParts): bool
    {
        return $this->returnError('error.userNotFound', ['chat.nick' => $textParts[0]]);
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
