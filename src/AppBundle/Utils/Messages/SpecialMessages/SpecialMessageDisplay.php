<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\SpecialMessages;

interface SpecialMessageDisplay
{
    /**
     * Display special message
     */
    public function display(string $text): array;
}