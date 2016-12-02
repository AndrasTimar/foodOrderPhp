<?php
/**
 * Created by PhpStorm.
 * User: bandi
 * Date: 12/2/2016
 * Time: 00:41
 */

namespace AppBundle\Service;


use Symfony\Component\Form\Test\FormInterface;

interface IAddressService
{

    /**
     * @param $address
     * @return FormInterface
     */
    public function getAddressForm($address);

    public function saveAddress($address);

    public function getAddressById($addressId);
}