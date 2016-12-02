<?php

namespace AppBundle\Repository;
use AppBundle\Entity\User;

/**
 * Created by PhpStorm.
 * User: bandi
 * Date: 11/30/2016
 * Time: 16:55
 */
class UserRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param $username
     * @param $hashedPassword
     * @return User
     */
    public function findByNameAndPassword($username, $hashedPassword){
       return $this->createQueryBuilder('u')
            ->where('u.username = :uname and u.password = :hashpass')
            ->setParameter('uname', "{$username}")
            ->setParameter('hashpass', "{$hashedPassword}")
            ->getQuery() ->getOneOrNullResult();
    }
}