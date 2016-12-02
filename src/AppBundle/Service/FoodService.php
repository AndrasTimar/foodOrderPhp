<?php
/**
 * Created by PhpStorm.
 * User: bandi
 * Date: 12/2/2016
 * Time: 13:38
 */

namespace AppBundle\Service;


use AppBundle\Entity\Food;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Test\FormInterface;

class FoodService implements IFoodService
{
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var EntityRepository
     */
    private $foodRepository;

    /**
     * @var FormFactory
     */
    private $formFactory;
    /**
     * @return Food[]
     */
    public function getAllFoods()
    {
        return $this->foodRepository->findAll();
    }

    public function __construct(EntityManager $entityManager, FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->foodRepository = $entityManager->getRepository(Food::class);
    }

    public function getFoodById($foodId)
    {
        return $this->foodRepository->find($foodId);
    }

    /**
     * @param $food Food
     * @return FormInterface
     */
    public function getFoodForm($food)
    {
        $form = $this->formFactory->createBuilder(FormType::class, $food);

        $form->add("name", TextType::class);
        $form->add("cost", NumberType::class);
        $form->add("available", ChoiceType::class,
            array("choices"=>array("YES"=>true, "NO"=>false))
        );
        $form->add("description", TextareaType::class);
        $form->add("save", SubmitType::class, array('label'=>'Save Food'));

        return $form->getForm();
    }

    /**
     * @param $foodId integer
     */
    public function deleteFood($foodId)
    {
        $food = $this->getFoodById($foodId);
        $this->entityManager->remove($food);
        $this->entityManager->flush();
    }

    /**
     * @param $food Food
     */
    public function saveFood($food)
    {
        $this->entityManager->persist($food);
        $this->entityManager->flush();
    }
}