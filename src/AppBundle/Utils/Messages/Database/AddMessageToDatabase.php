<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\Database;

use AppBundle\Entity\Message;
use AppBundle\Entity\User;
use AppBundle\Repository\MessageRepository;
use AppBundle\Utils\Cache\GetBotUserFromCache;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class AddMessageToDatabase
{
    /**
     * @var GetBotUserFromCache
     */
    private $botUserFromCache;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        GetBotUserFromCache $botUserFromCache,
        EntityManagerInterface $em,
        RequestStack $requestStack
    ) {
        $this->botUserFromCache = $botUserFromCache;
        $this->em = $em;
        $this->requestStack = $requestStack;
    }

    public function addBotMessage(string $text, int $channel): void
    {
        if ($this->requestStack->getCurrentRequest() === null) {
            throw new \RuntimeException('Could not find request');
        }
        $bot = $this->botUserFromCache->getChatBotUser();
        /** @var MessageRepository $repository */
        $repository = $this->em->getRepository(Message::class);
        $repository->addBotMessage(
            $text,
            $channel,
            $bot,
            $this->requestStack->getCurrentRequest()->server->get('REMOTE_ADDR')
        );
    }

    /**
     * @param string $text
     * @param int $channel
     * @param User $user
     *
     * @throws \Exception
     */
    public function addMessage(string $text, int $channel, User $user): void
    {
        if ($this->requestStack->getCurrentRequest() === null) {
            throw new \RuntimeException('Could not find request');
        }
        $message = (new Message())->setIp($this->requestStack->getCurrentRequest()->server->get('REMOTE_ADDR'))
            ->setChannel($channel)
            ->setDate(new DateTime())
            ->setUserInfo($user)
            ->setText($text);
        $this->em->persist($message);
        $this->em->flush();
    }
}
