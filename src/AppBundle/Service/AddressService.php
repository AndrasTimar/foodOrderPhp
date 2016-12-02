<?php
/**
 * Created by PhpStorm.
 * User: bandi
 * Date: 12/2/2016
 * Time: 00:41
 */

namespace AppBundle\Service;


use AppBundle\Entity\Address;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactory;

class AddressService implements IAddressService
{
    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $addressRepository;

    /**
     * AddressService constructor.
     * @param FormFactory $formFactory
     * @param EntityManager $entityManager
     * @param EntityRepository $addressRepository
     */
    public function __construct(EntityManager $entityManager, FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->addressRepository = $entityManager->getRepository(Address::class);
    }

    /**
     * @param $address Address
     * @return \Symfony\Component\Form\FormInterface
     */



    public function getAddressForm($address)
    {
        $form = $this->formFactory->createBuilder(FormType::class, $address);

        $form->add("zip", NumberType::class);
        $form->add("city", TextType::class);
        $form->add("street", TextType::class);
        $form->add("houseNum", TextType::class);
        $form->add("door", TextType::class);

        $form->add("save", SubmitType::class, array('label'=>'Save Address'));

        return $form->getForm();
    }

    public function saveAddress($address)
    {
       $this->entityManager->persist($address);
    }

    public function getAddressById($addressId)
    {
        return $this->addressRepository->find($addressId);
    }
}