<?php
/**
 * Created by PhpStorm.
 * User: bandi
 * Date: 12/2/2016
 * Time: 15:01
 */

namespace AppBundle\Service;


use AppBundle\Entity\Food;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderItem;
use Symfony\Component\Form\Test\FormInterface;

interface IOrderService
{

    /**
     * @param $orderItem OrderItem
     * @return FormInterface
     */
    public function getOrderItemForm($orderItem);

    /**
     * @param $order Order
     * @return
     */
    public function saveOrder($order);

    /**
     * @param $orderId integer
     * @return Order
     */
    public function getOrderById($orderId);

    /**
     * @return Order[]
     */
    public function getAllOrders();

    /**
     * @param $order Order
     */
    public function deleteOrder($order);
}