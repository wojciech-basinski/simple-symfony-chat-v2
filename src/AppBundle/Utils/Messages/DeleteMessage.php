<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages;

use AppBundle\Entity\Message;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DeleteMessage
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
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        EntityManagerInterface $em,
        SessionInterface $session,
        RequestStack $requestStack
    ) {
        $this->em = $em;
        $this->session = $session;
        $this->requestStack = $requestStack;
    }
    /**
     * Deleting message from database
     *
     * @param int $id Message's id
     *
     * @param User $user User instance
     *
     * @return int status of deleting messages
     * @throws \Exception
     */
    public function deleteMessage(int $id, User $user): int
    {
        $channel = $this->session->get('channel');
        $message = $this->em->getRepository(Message::class)->find($id);
        if ($message === null) {
            return 0;
        }
        $this->em->remove($message);
        $this->em->flush();

        if ($this->requestStack->getCurrentRequest() === null) {
            throw new \RuntimeException('Could not find request');
        }
        $message = new Message();
        $message->setUserInfo($user)
            ->setChannel($channel)
            ->setText('/delete ' . $id)
            ->setDate(new \DateTime())
            ->setIp($this->requestStack->getCurrentRequest()->server->get('REMOTE_ADDR'));

        $this->em->persist($message);
        $this->em->flush();

        return 1;
    }
}
