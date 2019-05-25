<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\Validator;

use AppBundle\Entity\User;
use AppBundle\Utils\ChatConfig;
use function array_key_exists;
use function strlen;
use function strpos;
use function strtolower;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use function trim;

class AddMessageValidator
{
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var ChatConfig
     */
    private $config;

    public function __construct(SessionInterface $session, ChatConfig $config)
    {
        $this->session = $session;
        $this->config = $config;
    }

    /**
     * Validating if message is valid (not empty etc.) or User and Channel exists
     *
     * @param User $user User instance
     *
     * @param int $channel Channel's id
     *
     * @param string $text message text
     *
     * @return bool status
     */
    public function validateMessage(User $user, int $channel, ?string $text): bool
    {
        if ($text === null) {
            $this->session->set('errorMessage', 'Wiadomośc nie może być pusta');
            return false;
        }
        $text = strtolower(trim($text));
        if (strlen($text) <= 0) {
            $this->session->set('errorMessage', 'Wiadomośc nie może być pusta');
            return false;
        }
        if ($user->getId() <= 0) {
            $this->session->set('errorMessage', 'Nie możesz wysyłać wiadomości będąc nie zalogowanym');
            return false;
        }
        if (!array_key_exists($channel, $this->config->getChannels($user))) {
            $this->session->set('errorMessage', 'Nie możesz pisać na tym kanale');
            return false;
        }
        if (strpos($text, '(pm)') === 0) {
            $this->session->set('errorMessage', 'Wiadomośc nie może zaczynać się od (pm)');
            return false;
        }
        if (strpos($text, '(pw)') === 0) {
            $this->session->set('errorMessage', 'Wiadomośc nie może zaczynać się od (pw)');
            return false;
        }
        return true;
    }
}
