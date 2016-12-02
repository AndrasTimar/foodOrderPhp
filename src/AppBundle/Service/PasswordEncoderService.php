<?php
/**
 * Created by PhpStorm.
 * User: bandi
 * Date: 12/1/2016
 * Time: 23:01
 */

namespace AppBundle\Service;


class PasswordEncoderService implements IPasswordEncoderService
{
    /**
     * @param $pass string
     * @return string
     */
    function hashPass($pass)
    {
        $salt = "1237964892adjnhafil7f3hjfs";
        return md5($pass.$salt);
    }
}