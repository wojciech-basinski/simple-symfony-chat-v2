<?php
namespace AppBundle\Repository;

class UserRepository extends \Doctrine\ORM\EntityRepository
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