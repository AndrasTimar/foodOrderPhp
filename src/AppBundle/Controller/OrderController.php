<?php
/**
 * Created by PhpStorm.
 * User: bandi
 * Date: 12/2/2016
 * Time: 15:01
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Order;
use AppBundle\Entity\OrderItem;
use AppBundle\Service\IOrderService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends Controller
{
    /**
     * @var IOrderService
     */
    private $orderService;

    /**
     * @var Order
     */
    private $order;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->orderService=$this->get("app.order_service");
    }

    /**
     * @Route("/order/{foodId}", name="addfood")
     */
    public function addFood($foodId=0, Request $request){
        $this->order = $request->getSession()->get("order");
        return new Response();
    }
}