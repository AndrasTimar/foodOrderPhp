<?php
/**
 * Created by PhpStorm.
 * User: bandi
 * Date: 12/2/2016
 * Time: 15:01
 */

namespace AppBundle\Service;

use AppBundle\Entity\Address;
use AppBundle\Entity\Food;
use AppBundle\Entity\Order;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Swift_Message;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Test\FormInterface;

class OrderService implements IOrderService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $orderRepository;

    /**
     * @var FormFactory
     */
    private $formFactory;
    /**
     * @var \Swift_Mailer
     */
    private $mailerService;

    /**
     * @var EngineInterface
     */
    private $templating;

    private $addressService;
    /**
     * AuthenticationService constructor.
     * @param $entityManager EntityManager
     * @param $formFactory FormFactory
     */
    public function __construct(EntityManager $entityManager,FormFactory $formFactory, \Swift_Mailer $mailerService, EngineInterface $templating, AddressService $addressService )
    {
        $this->entityManager = $entityManager;
        $this->orderRepository = $entityManager->getRepository(Order::class);
        $this->formFactory = $formFactory;
        $this->mailerService = $mailerService;
        $this->templating = $templating;
        $this->addressService = $addressService;

    }

    /**
     * @param $orderItem $orderItem
     * @return FormInterface
     */
    public function getOrderItemForm($orderItem)
    {
        $form = $this->formFactory->createBuilder(FormType::class, $orderItem);
        $form->add("amount", NumberType::class);
        $form->add("food", EntityType::class, array(
            'class' => 'AppBundle:Food',
            'query_builder' => function(EntityRepository $repository) {
               return $repository->createQueryBuilder("f")
                    ->where("f.available = 1");
             },
            'choice_label' => function (Food $food) {
                return $food->getName()." | ".$food->getCost()." Ft";},
            'choice_value' => 'id'
        ));

        $form->add("send", SubmitType::class, array('label'=>'Save'));
        return $form->getForm();
    }

    /**
     * @param $order Order
     */
    public function saveOrder($order)
    {
        $this->entityManager->merge($order);
        $this->entityManager->flush();
    }

    public function getOrderById($orderId)
    {
        return $this->orderRepository->find($orderId);
    }

    /**
     * @return Order[]
     */
    public function getAllOrders()
    {
        return $this->orderRepository->findBy(array(), array('orderdate' => 'DESC'));
    }

    /**
     * @param $order Order
     */
    public function deleteOrder($order)
    {
        $this->entityManager->remove($order);
        $this->entityManager->flush();
    }

    public function deliverOrder(Order $order)
    {
        $order->setDeliverDate(new \DateTime(date("Y-m-d H:i:s")));
        $this->saveOrder($order);

        $message = Swift_Message::newInstance('Order Delivered')
            ->setFrom(array('foodorder.oe@gmail.com' => 'Food Order'))
            ->setTo(array($order->getUser()->getEmail()))
            ->setBody($this->templating->render("email/deliverOrder.email.html.twig",["order"=> $order]));

        $this->mailerService->send($message);
    }

    /**
     * @param $address
     * @param $orderItems
     */
    public function placeOrder($address, $orderItems, User $user)
    {
        $order = new Order();
        $order->setUser($user);

        $order->setAddress($this->addressService->getAddressById($address));
        foreach ($orderItems as $orderItem) {
            $order->getOrderItem()->add($orderItem);
        }
        $order->setOrderDate(new \DateTime(date("Y-m-d H:i:s")));
        $this->saveOrder($order);

        //define credentials in config.yml
        $message = Swift_Message::newInstance('Order Sent')
            ->setFrom(array('foodorder.oe@gmail.com' => 'Food Order'))
            ->setTo(array($user->getEmail()))
            ->setBody($this->templating->render("email/placeOrder.email.html.twig",["order"=> $order]));
        $this->mailerService->send($message);
    }
}