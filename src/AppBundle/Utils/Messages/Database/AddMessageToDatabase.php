<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\Database;

use AppBundle\Entity\Message;
use AppBundle\Entity\User;
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
     * @var Request
     */
    private $request;

    public function __construct(
        GetBotUserFromCache $botUserFromCache,
        EntityManagerInterface $em,
        RequestStack $requestStack
    ) {
        $this->botUserFromCache = $botUserFromCache;
        $this->em = $em;
        $this->request = $requestStack->getCurrentRequest();
    }

    public function addBotMessage(string $text, int $channel): void
    {
        $bot = $this->botUserFromCache->getChatBotUser();
        $this->em->getRepository(Message::class)
            ->addBotMessage($text, $channel, $bot, $this->request->server->get('REMOTE_ADDR'));
    }

    public function addMessage(string $text, int $channel, User $user): void
    {
        $message = (new Message())->setIp($this->request->server->get('REMOTE_ADDR'))
            ->setChannel($channel)
            ->setDate(new DateTime())
            ->setUserInfo($user)
            ->setText($text);
        $this->em->persist($message);
        $this->em->flush();
    }
}
