<?php

namespace  AppBundle\Service;
use AppBundle\Entity\Address;
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
     * @param $uname string
     * @param $upass string
     * @return User
     */
    function login($uname, $upass);


    /**
     * @param UserDTO
     * @return FormInterface
     */
    function getLoginForm($userDTO);

    /**
     * @param $user User
     * @return FormInterface
     */
    public function getRegForm($user);

    /**
     * @param $user User
     * @return boolean
     */
    function register($user, $userId);

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
}