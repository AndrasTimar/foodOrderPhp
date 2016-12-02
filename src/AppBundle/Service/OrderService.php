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
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            'choice_label' => function ($food) {
                return $food->getName()."|".$food->getCost()." Ft";},
            'choice_value' => 'id'
        ));

        $form->add("register", SubmitType::class, array('label'=>'Save'));
        return $form->getForm();
    }
}