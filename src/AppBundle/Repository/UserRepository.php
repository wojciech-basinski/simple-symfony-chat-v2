<?php declare(strict_types = 1);

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function getBannedUsers(): array
    {
        return $this->_em->createQueryBuilder()
            ->select('u')
            ->from('AppBundle:User', 'u')
            ->where('u.banned IS NOT NULL')
            ->getQuery()
            ->getResult();
    }
}
