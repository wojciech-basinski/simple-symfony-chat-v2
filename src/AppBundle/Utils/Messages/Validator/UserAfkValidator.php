<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\Validator;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UserAfkValidator
{
    public const AFK_MESSAGES_KEYS = [
        '/afk',
        '/zw',
        '/jj'
    ];

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function validateUserAfk(string $text): bool
    {
        return $this->session->get('afk') && !in_array($text, self::AFK_MESSAGES_KEYS, true);
    }
}