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
use AppBundle\Entity\User;
use AppBundle\Service\IAddressService;
use AppBundle\Service\IFoodService;
use AppBundle\Service\IOrderService;
use AppBundle\Service\IUserService;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends Controller
{
    /**
     * @var IOrderService
     */
    private $orderService;

    /**
     * @var IFoodService
     */
    private $foodService;

    /**
     * @var IUserService
     */
    private $userService;

    /**
     * @var \Swift_Mailer
     */
    private $mailerService;

    /**
     * @var IAddressService
     */
    private $addressService;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->orderService=$this->get("app.order_service");
        $this->userService=$this->get("app.user_service");
        $this->foodService=$this->get("app.food_service");
        $this->mailerService=$this->get("mailer");
        $this->addressService=$this->get("app.address_service");
    }

    /**
     * @Route("/cart/add/{foodId}", name="cartaddfood")
     */
    public function addFood($foodId=0, Request $request){
        $user = $this->getUser();
        $food = $this->foodService->getFoodById($foodId);

        /** @var OrderItem $orderItem */
        $orderItem = new OrderItem();
        $orderItem->setFood($food);
        $orderItem->setAmount(1);

        $formInterface = $this->orderService->getOrderItemForm($orderItem);

        $formInterface->handleRequest($request);

        if($formInterface->isSubmitted() && $formInterface->isValid()){
            //pretty ugly...
            $orderItems = $request->getSession()->get("orderItems");
            $orderItems[uniqid()] = $orderItem;
            $request->getSession()->set("orderItems",$orderItems);
            $this->addFlash('notice', 'Success!');
            return $this->redirectToRoute("cart");
        }

        return $this->render('FoodOrder/baseform.html.twig', array("form"=>$formInterface->createView()) );
    }
    /**
     * @Route("/cart/remove/{arrid}", name="cartremoveone")
     */
    public function removeFood($arrid = 0, Request $request){
        $orderItems = $request->getSession()->get("orderItems");
        if(isset($orderItems[$arrid])){
            unset($orderItems[$arrid]);
            $request->getSession()->set("orderItems",$orderItems);
            $this->addFlash('notice', 'Success!');
        }
        return $this->redirectToRoute("cart");
    }

    /**
     * @Route("/cart/view", name="cart")
     */
    public function viewCart(Request $request){
        /** @var OrderItem[] $arr */
        $arr = $request->getSession()->get("orderItems");
        if(!$arr){
            $arr = [];
        }
        $totalcost = 0;

        foreach($arr as $item){
            $totalcost += $item->getFood()->getCost() * $item->getAmount();
        }
        return $this->render(':FoodOrder:cartview.html.twig', array('itemList'=>$arr,"totalCost"=>$totalcost));
    }

    /**
     * @Route("/cart/clear", name="cartempty")
     */
    public function clearCart(Request $request){
        $request->getSession()->set("orderItems",array());
        $this->addFlash('notice', 'Success!');
        return $this->redirectToRoute("cart");
    }

    /**
     * @Route("/cart/send", name="sendorder")
     */
    public function placeOrder(Request $request){
        $orderItems = $request->getSession()->get("orderItems");
        if(sizeof($orderItems) == 0){
            $this->addFlash('notice', 'Cart is empty!');
            return $this->redirectToRoute("cart");
        }
        $address = $request->getSession()->get("address");
        if(!$address){
            $this->addFlash('notice', 'Please choose an address!');
            return $this->redirectToRoute("address_list");
        }

        /** @var Order $order */
        $order = new Order();
        $user = $this->getUser();
        $order->setUser($user);
        $order->setAddress($this->addressService->getAddressById($address));
        foreach($orderItems as $orderItem){
            $order->getOrderItem()->add($orderItem);
        }
        $order->setOrderDate(new \DateTime(date("Y-m-d H:i:s")));
        $this->orderService->saveOrder($order);

        //define credentials in config.yml
        $message = Swift_Message::newInstance('Order Sent')
            ->setFrom(array('foodorder.oe@gmail.com' => 'Food Order'))
            ->setTo(array($user->getEmail()))
            ->setBody('Your order was sent at '.$order->getOrderDate()->format("d/m/Y H:i:s").". It will arrive in 65 minutes.")
        ;
        $this->mailerService->send($message);

        $request->getSession()->set("orderItems",array());
        $this->addFlash('notice', 'Order Sent');
        return $this->redirectToRoute("foods");
    }

    /**
     * @Route("/orders/view", name="listorders")
     */
    public function getOrdersOfUser(Request $request){

        /** @var User $user */
        $user = $this->getUser();
        $arr = $user->getOrder();
        return $this->render(":FoodOrder:orderList.html.twig",["orders"=>$arr]);
    }

    /**
     * @Route("/orders/show/{orderId}", name="showorder")
     */
    public function showOrder(Request $request, $orderId){

        /** @var User $user */
        $user = $this->getUser();
        $order = $this->orderService->getOrderById($orderId);
        if($user == $order->getUser()) {
            return $this->render(":FoodOrder:ordersheet.html.twig", ["order" => $order]);
        }
        else{
            return $this->redirectToRoute('listorders');
        }
    }

    /**
     * @Route("/orders/admin/manage", name="manageorders")
     */
    public function manageOrders(Request $request){
        /** @var User $user */
        $arr = $this->orderService->getAllOrders();
        return $this->render(":FoodOrder:orderList.html.twig",["orders"=>$arr]);
    }

    /**
     * @Route("/orders/admin/deliver/{orderId}", name="deliverorder")
     */
    public function deliverOrder(Request $request, $orderId){
        $order = $this->orderService->getOrderById($orderId);
        $order->setDeliverDate(new \DateTime(date("Y-m-d H:i:s")));
        $this->orderService->saveOrder($order);

        $message = Swift_Message::newInstance('Order Delivered')
            ->setFrom(array('foodorder.oe@gmail.com' => 'Food Order'))
            ->setTo(array($order->getUser()->getEmail()))
            ->setBody('Your order was delivered at '.$order->getDeliverDate()->format("d/m/Y H:i:s").". Enjoy.");

        $this->mailerService->send($message);

        return $this->redirectToRoute("manageorders");

    }

    /**
     * @Route("/orders/admin/delete/{orderId}", name="deleteorder")
     */
    public function deleteOrder(Request $request, $orderId){

        $order = $this->orderService->getOrderById($orderId);
        $this->orderService->deleteOrder($order);

        return $this->redirectToRoute("manageorders");

    }
}