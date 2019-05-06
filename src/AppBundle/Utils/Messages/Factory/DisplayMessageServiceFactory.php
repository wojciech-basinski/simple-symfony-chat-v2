<?php declare(strict_types = 1);

namespace AppBundle\Utils\Messages\Factory;

use AppBundle\Utils\Messages\SpecialMessages\Roll\RollDisplay;
use AppBundle\Utils\Messages\SpecialMessages\SpecialMessageDisplay;

class DisplayMessageServiceFactory
{
    /**
     * @var RollDisplay
     */
    private $rollDisplay;

    public function __construct(RollDisplay $rollDisplay)
    {
        $this->rollDisplay = $rollDisplay;
    }

    public function getDisplayService(string $text): ?SpecialMessageDisplay
    {
        $textSplitted = explode(' ', $text, 2);

        switch ($textSplitted[0]) {
            case '/roll':
                return $this->rollDisplay;
//            case '/privTo':
//                return $this->privToShow($textSplitted);
//            case '/privMsg':
//                return $this->privFromShow($textSplitted);
//            case '/invite':
//                return $this->inviteToShow($textSplitted);
//            case '/uninvite':
//                return $this->uninviteToShow($textSplitted);
//            case '/afk':
//                return $this->afkToShow($textSplitted);
//            case '/returnAfk':
//                return $this->returnAfkToShow($textSplitted);
            default:
                return null;
        }

    }
}