<?php declare(strict_types = 1);

namespace AppBundle\Utils;

use AppBundle\Entity\User;
use AppBundle\Utils\Messages\SpecialMessages;
use AppBundle\Utils\Messages\Transformers\MessageToArrayTransformer;
use AppBundle\Utils\Messages\Validator\MessageDisplayValidator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use AppBundle\Entity\Message as MessageEntity;

/**
 * Service to preparing messages from database to array or check if new message can be add to database
 *
 * Class Message
 * @package AppBundle\Utils
 */
class Message
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
     * @var ChatConfig
     */
    private $config;
    /**
     * @var SpecialMessages
     */
    private $specialMessages;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var MessageToArrayTransformer
     */
    private $messageToArrayTransformer;
    /**
     * @var MessageDisplayValidator
     */
    private $messageDisplayValidator;

    public function __construct(
        EntityManagerInterface $em,
        SessionInterface $session,
        ChatConfig $config,
        SpecialMessages $special,
        LoggerInterface $logger,
        RequestStack $request,
        MessageToArrayTransformer $messageToArrayTransformer,
        MessageDisplayValidator $messageDisplayValidator
    ) {
        $this->em = $em;
        $this->session = $session;
        $this->config = $config;
        $this->specialMessages = $special;
        $this->logger = $logger;
        $this->request = $request->getCurrentRequest();
        $this->messageToArrayTransformer = $messageToArrayTransformer;
        $this->messageDisplayValidator = $messageDisplayValidator;
    }




}
