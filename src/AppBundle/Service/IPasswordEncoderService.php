<?php
/**
 * Created by PhpStorm.
 * User: bandi
 * Date: 12/1/2016
 * Time: 23:02
 */

namespace AppBundle\Service;


interface IPasswordEncoderService
{

    /**
     * @param $pass string
     * @return string
     */
    function hashPass($pass);
}