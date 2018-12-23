<?php
namespace AppBundle\Utils;

use AppBundle\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\Message;

class Logs
{
    /**
     * @var MessageRepository
     */
    private $messageRepository;
    /**
     * @var ChatConfig
     */
    private $config;

    public function __construct(EntityManagerInterface $em, ChatConfig $config)
    {
        $this->messageRepository = $em->getRepository(Message::class);
        $this->config = $config;
    }

    public function getLogs(string $start, string $end): array
    {
        [$dateStart, $dateEnd] = $this->createDates($start, $end);

        if ($dateEnd < $dateStart) {
            [$dateStart, $dateEnd] = [$dateEnd, $dateStart];
        }

        $messages = $this->messageRepository->findBetweenTwoDates(
            $dateStart,
            $dateEnd,
            $this->config->getPrivateMessageAdd()
        );
        return $messages;
    }

    private function createDates(string $start, string $end): array
    {
        $start = \DateTime::createFromFormat('d.m.Y H:i', $start);
        $end = \DateTime::createFromFormat('d.m.Y H:i', $end);

        if ($start === false) {
            $start = new \DateTime('now');
        }if ($end === false) {
            $end = new \DateTime('now');
        }

        return [$start, $end];
    }
}