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
     * @var Order
     */
    private $order;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->orderService=$this->get("app.order_service");
        $this->userService=$this->get("app.user_service");
        $this->foodService=$this->get("app.food_service");
        $this->mailerService=$this->get("mailer");
    }

    /**
     * @Route("/order/{foodId}", name="addfood")
     */
    public function addFood($foodId=0, Request $request){
        $userId = $request->getSession()->get("userId");
        if(!$userId){
            $this->addFlash('notice', 'Please log in!');
            return $this->redirectToRoute("login");
        }
        $user = $this->userService->getUserById($userId);
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
            $orderItems[] = $orderItem;
            $request->getSession()->set("orderItems",$orderItems);
            $this->addFlash('notice', 'Success!');
            return $this->redirectToRoute("cart");
        }

        return $this->render('FoodOrder/baseform.html.twig', array("form"=>$formInterface->createView(),"loggedIn"=>true,"admin"=>$user->getAdmin()) );
    }

    /**
     * @Route("/cart", name="cart")
     */
    public function viewCart(Request $request){
        $userId = $request->getSession()->get("userId");
        if(!$userId){
            $this->addFlash('notice', 'Please log in!');
            return $this->redirectToRoute("login");
        }

        $user = $this->userService->getUserById($userId);
        $arr = $request->getSession()->get("orderItems");
        $totalcost = 0;
        foreach($arr as $item){
            $totalcost += $item->getFood()->getCost() * $item->getAmount();
        }
        return $this->render(':FoodOrder:cartview.html.twig', array('itemList'=>$arr,"loggedIn"=>true,"admin"=>$user->getAdmin(),"totalCost"=>$totalcost));
    }

    /**
     * @Route("/cart/clear", name="cartempty")
     */
    public function clearCart(Request $request){
        $userId = $request->getSession()->get("userId");
        if(!$userId){
            $this->addFlash('notice', 'Please log in!');
            return $this->redirectToRoute("login");
        }
        $request->getSession()->set("orderItems",array());
        $this->addFlash('notice', 'Success!');
        return $this->redirectToRoute("cart");
    }

    /**
     * @Route("/cart/send", name="sendorder")
     */
    public function placeOrder(Request $request){
        $userId = $request->getSession()->get("userId");
        if(!$userId){
            $this->addFlash('notice', 'Please log in!');
            return $this->redirectToRoute("login");
        }
        $orderItems = $request->getSession()->get("orderItems");
        if(sizeof($orderItems) == 0){
            $this->addFlash('notice', 'Cart is empty!');
            return $this->redirectToRoute("cart");
        }

        /** @var Order $order */
        $order = new Order();
        $user = $this->userService->getUserById($userId);
        $order->setUser($user);
        $order->setOrderDate(date('Y-m-d H:m:s'));
        foreach($orderItems as $orderItem){
            $this->addFlash('notice',$orderItem->getFood()->getId());
            $order->getOrderItem()->add($orderItem);
        }
        $this->orderService->saveOrder($order);
        $message = Swift_Message::newInstance('Order Sent')
            ->setFrom(array('foodorder.oe@gmail.com' => 'Food Order'))
            ->setTo(array($user->getEmail()))
            ->setBody('Your order was sent at '.$order->getOrderDate().". It will arrive in 65 minutes.")
        ;
        $this->mailerService->send($message);

        $request->getSession()->set("orderItems",array());
        $this->addFlash('notice', 'Order Sent');
        return $this->redirectToRoute("foods");
    }
}