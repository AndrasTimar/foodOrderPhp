<?php
/**
 * Created by PhpStorm.
 * User: bandi
 * Date: 12/2/2016
 * Time: 13:38
 */

namespace AppBundle\Service;


use AppBundle\Entity\Food;
use Symfony\Component\Form\Test\FormInterface;

interface IFoodService
{

    /**
     * @return Food[]
     */
    public function getAllFoods();

    /**
     * @param $foodId integer
     * @return Food
     */
    public function getFoodById($foodId);

    /**
     * @param $food Food
     * @return FormInterface
     */
    public function getFoodForm($food);

    /**
     * @param $food Food
     */
    public function saveFood($food);

    /**
     * @param $foodId integer
     */
    public function deleteFood($foodId);

}