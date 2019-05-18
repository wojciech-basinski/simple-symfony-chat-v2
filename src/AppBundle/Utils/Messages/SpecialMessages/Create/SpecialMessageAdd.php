<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\SpecialMessages\Create;

use AppBundle\Entity\User;

interface SpecialMessageAdd
{
    /**
     * Add special message
     *
     * @param array $text
     * @param User $user
     * @param int $channel
     *
     * @return bool
     */
    public function add(array $text, User $user, int $channel): bool;
}
