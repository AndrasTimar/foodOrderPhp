<?php
/**
 * Created by PhpStorm.
 * User: tesztbandi
 * Date: 2016. 12. 15.
 * Time: 18:35
 */

namespace AppBundle\util;


use Symfony\Component\HttpFoundation\Request;

class RequestUtil
{
    public static function getReferer(Request $request)
    {
        return $request
            ->headers
            ->get('referer');
    }
}