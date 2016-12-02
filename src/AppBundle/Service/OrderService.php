<?php
/**
 * Created by PhpStorm.
 * User: bandi
 * Date: 12/2/2016
 * Time: 15:01
 */

namespace AppBundle\Service;


use AppBundle\Entity\Order;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactory;

class OrderService implements IOrderService
{
    private $entityManager;

    private $orderRepository;

    /**
     * AuthenticationService constructor.
     * @param $entityManager EntityManager
     * @param $formFactory FormFactory
     */
    public function __construct(EntityManager $entityManager,FormFactory $formFactory)
    {
        $this->entityManager = $entityManager;
        $this->orderRepository = $entityManager->getRepository(Order::class);
        $this->formFactory = $formFactory;
    }
}