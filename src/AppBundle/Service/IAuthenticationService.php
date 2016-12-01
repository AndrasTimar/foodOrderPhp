<?php

namespace  AppBundle\Service;
use Symfony\Component\Form\FormInterface;

/**
 * Created by PhpStorm.
 * User: bandi
 * Date: 11/30/2016
 * Time: 16:49
 */
interface IAuthenticationService
{
    /**
     * @param $uname string
     * @param $upass string
     * @return boolean
     */
    function login($uname, $upass);

    /**
     * @param $uname string
     * @param $upass string
     * @return boolean
     */
    function register($uname, $upass);

    /**
     * @param $pass string
     * @return string
     */
    function hashPass($pass);

    /**
     * @return FormInterface
     */
    function getLoginForm();
}