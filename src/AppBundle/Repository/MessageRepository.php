<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use AppBundle\Utils\ChatConfig;

/**
 * MessageRepository
 *
 */
class MessageRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Gets Messages from database from last 24h ordered by date descending
     *
     * @param int $channel Channel's id
     * @param int $channelPrivateMessage private channel's id
     *
     * @return array|null Array of Messages Entity of null if no messages
     */
    public function getMessagesFromLastDay(int $channel, int $channelPrivateMessage): array
    {
        $date = $this->getDateOneDayEarlier();

        $messages =  $this->createQueryBuilder('m')
            ->where('m.date >= :date')
            ->andWhere('(m.channel = :channel AND m.text NOT LIKE :text) OR (m.channel = :channelPrivate AND 
                m.text LIKE :textPrivate)')
            ->orderBy('m.date', 'DESC')
            ->setParameter('date', $date)
            ->setParameter('channel', $channel)
            ->setParameter('channelPrivate', $channelPrivateMessage)
            ->setParameter('text', '/delete%')
            ->setParameter('textPrivate', '/%')
            ->setMaxResults(100)
	    ->getQuery()
            ->getResult();

        return $this->sortByDateAsc($messages);
    }

    /**
     * Gets Messages from database from last id ordered by id asscending
     *
     * @param int $lastId last message's id
     * @param int $channelMessagePrefix Private message prefix
     * @param int $channelPrivateMessage
     *
     * @return array|null Array of Messages or null if no messages
     */
    public function getMessagesFromLastId(int $lastId, int $channelMessagePrefix, int $channelPrivateMessage): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.id > :id')
            ->andWhere('(m.channel < :channel) OR (m.channel = :channelPrivate AND m.text LIKE :textPrivate)')
            ->orderBy('m.id', 'ASC')
            ->setParameter('id', $lastId)
            ->setParameter('channel', $channelMessagePrefix)
            ->setParameter('channelPrivate', $channelPrivateMessage)
            ->setParameter('textPrivate', '/%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets messages between two ids when sending new message and there was new messages
     *
     * @param int $idFirst beginning of the interval
     * @param int $idSecond End of interval
     * @param int $channel Channel's id
     * @param int $channelPrivateMessage
     *
     * @return array|null Array of messages or null if no messages
     */
    public function getMessagesBetweenIds(int $idFirst, int $idSecond, int $channel, int $channelPrivateMessage): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.id BETWEEN :id1 AND :id2')
            ->andWhere('m.channel = :channel OR (m.channel = :channelPrivate AND m.text LIKE :textPrivate)')
            ->orderBy('m.id', 'ASC')
            ->setParameter('id1', $idFirst)
            ->setParameter('id2', $idSecond)
            ->setParameter('textPrivate', "/%")
            ->setParameter('channel', $channel)
            ->setParameter('channelPrivate', $channelPrivateMessage)
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets id of only last message on chat
     *
     * @return int message's id
     */
    public function getIdFromLastMessage(): int
    {
        $message = $this->createQueryBuilder('m')
            ->orderBy('m.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        if ($message) {
            return $message->getId();
        } else {
            return 0;
        }
    }

    /**
     * Deletes message from chat
     *
     * @param $id message's id
     *
     * @return int status of deleting
     */
    public function deleteMessage(int $id): int
    {
        return $this->createQueryBuilder('m')
            ->delete()
            ->where('m.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

    public function findBetweenTwoDates(\DateTime $start, \DateTime $end, int $privChannel, ?User $user): array
    {
        $em = $this->createQueryBuilder('m')
            ->where('m.date >= :start')
            ->andWhere('m.date <= :end')
            ->andWhere('m.channel < :privChannel');
        if ($user) {
            $em->andWhere('m.userId = :id')
                ->setParameter('id', $user->getId());
        }
        $em->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('privChannel', $privChannel);
        return $em->getQuery()
            ->getResult();
    }

    private function getDateOneDayEarlier(): \DateTime
    {
        $date = new \DateTime('now');
        $date->modify('-1 day');
        return $date;
    }

    private function sortByDateAsc(array $messages): array
    {
        uasort($messages, function($a, $b) {
            return $a->getDate() <=> $b->getDate();
        });

        $return = [];
        foreach ($messages as $key => $value) {
            $return[] = $value;
        }
        return $return;
    }

}
