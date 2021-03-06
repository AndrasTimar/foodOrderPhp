<?php

namespace  AppBundle\Service;
use AppBundle\Entity\Address;
use AppBundle\Entity\Group;
use AppBundle\Entity\User;
use Symfony\Component\Form\FormInterface;

/**
 * Created by PhpStorm.
 * User: bandi
 * Date: 11/30/2016
 * Time: 16:49
 */
interface IUserService
{

    /**
     * @param $user User
     * @return FormInterface
     */
    public function getAdminEditForm($user);

    /**
     * @param $username String
     * @return User
     */
    public function getUserByName($username);

    /**
     * @param $userId integer
     * @return User
     */
    public function getUserById($userId);

    /**
     * @param $user User
     */
    public function deleteUser($user);

    /**
     * @return User[]
     */
    public function getAllUsers();

    public function promoteToAdmin($user);

    public function persistUser($user);

    public function demoteToUser($userId);
}