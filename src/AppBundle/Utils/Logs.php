<?php declare(strict_types = 1);

namespace AppBundle\Utils;

use AppBundle\Entity\User;
use AppBundle\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\Message;

class Logs
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var ChatConfig
     */
    private $config;

    public function __construct(EntityManagerInterface $em, ChatConfig $config)
    {
        $this->em = $em;
        $this->config = $config;
    }

    public function getLogs(string $start, string $end, string $userName): array
    {
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->em->getRepository(Message::class);
        [$dateStart, $dateEnd] = $this->createDates($start, $end);

        if ($dateEnd < $dateStart) {
            [$dateStart, $dateEnd] = [$dateEnd, $dateStart];
        }

        $user = $this->getUser($userName);

        $messages = $messageRepository->findBetweenTwoDates(
            $dateStart,
            $dateEnd,
            $this->config->getPrivateMessageAdd(),
            $user
        );
        return $messages;
    }

    private function getUser(string $userName): ?User
    {
        if ($userName === '') {
            return null;
        }
        /** @var User|null $user */
        $user = $this->em->getRepository(User::class)->findOneBy(['username' => $userName]);
        return $user;
    }

    private function createDates(string $start, string $end): array
    {
        $start = \DateTime::createFromFormat('d.m.Y H:i', $start);
        $end = \DateTime::createFromFormat('d.m.Y H:i', $end);

        if ($start === false) {
            $start = new \DateTime('now');
        }
        if ($end === false) {
            $end = new \DateTime('now');
        }

        return [$start, $end];
    }
}
