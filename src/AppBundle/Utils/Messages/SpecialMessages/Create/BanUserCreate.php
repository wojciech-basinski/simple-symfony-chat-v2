<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\SpecialMessages\Create;

use AppBundle\Entity\User;
use AppBundle\Entity\UserOnline;
use AppBundle\Utils\ChatConfig;
use AppBundle\Utils\Messages\Database\AddMessageToDatabase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BanUserCreate implements SpecialMessageAdd
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

        if (!isset($textSplitted[1]) || !count($textParts = explode(' ', $textSplitted[1], 3))) {
            return $this->wrongUsernameError();
        }
        /** @var User $userToBan */
        $userToBan = $this->em->getRepository(User::class)->findOneByUsername($textParts[0]);

        return $this->banUser($userToBan, $user, $textParts);
    }

    private function banUser(?User $userToBan, User $user, array $textParts): bool
    {
        if ($userToBan === null) {
            return $this->userNotFoundError($textParts);
        }
        if (\in_array($userToBan->getChatRoleAsText(), ['administrator', 'demotywatorking'])) {
            return $this->cantBanAdminError();
        }
        if ($userToBan->getId() === $user->getId()) {
            return $this->cantBanYourselfError();
        }
        $banLength = $this->calculateBanLength($textParts);
        $reason = $this->getReason($textParts);

        $userToBan->setBanReason($reason)
            ->setBanned(new \DateTime("now + $banLength sec"));
        $this->em->persist($userToBan);
        $userOnline = $this->em->getRepository(UserOnline::class)->findOneBy(['userId' => $userToBan->getId()]);
        if ($userOnline) {
            $this->em->remove($userOnline);
        }
        $this->em->flush();

        $this->addMessageToDatabase->addBotMessage(
            '/banned ' . $userToBan->getUsername(),
            $this->config->getUserPrivateMessageChannelId($user)
        );
        return true;
    }

    private function calculateBanLength(array $textParts): int
    {
        return count($textParts) > 1 && is_numeric($textParts[1]) ? $textParts[1] * 60 : 360;
    }

    private function getReason(array $textParts): string
    {
        return \count($textParts) > 2 ? $textParts[2] : '';
    }

    private function permissionDenied(): bool
    {
        return $this->returnError('error.notPermittedToBan');
    }

    private function wrongUsernameError(): bool
    {
        return $this->returnError('error.wrongUsername');
    }

    private function userNotFoundError(array $textParts): bool
    {
        return $this->returnError('error.userNotFound', ['chat.nick' => $textParts[0]]);
    }

    private function cantBanAdminError(): bool
    {
        return $this->returnError('error.cantBanAdmin');
    }

    private function cantBanYourselfError(): bool
    {
        return $this->returnError('error.cantBanYourself');
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
