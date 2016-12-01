<?php

namespace  AppBundle\Service;
use AppBundle\AppBundle;
use AppBundle\Entity\User;
use AppBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;

/**
 * Created by PhpStorm.
 * User: bandi
 * Date: 11/30/2016
 * Time: 16:52
 */
class AuthenticationService implements IAuthenticationService
{

    private $entityManager;
    private $formFactory;
    /**
     * @var UserRepository
     */
    private $userRepo;
    /**
     * AuthenticationService constructor.
     * @param $entityManager EntityManager
     * @param $formFactory FormFactory
     */
    public function __construct(EntityManager $entityManager,FormFactory $formFactory)
    {
        $this->entityManager = $entityManager;
        $this->userRepo = $entityManager->getRepository(User::class);
        $this->formFactory = $formFactory;
    }


    /**
     * @param $uname string
     * @param $upass string
     * @return boolean
     */
    function login($uname, $upass)
    {
        $found = $this->userRepo->findByNameAndPassword($uname,$this->hashPass($upass));
        if($found){
            return true;
        }
        return false;
    }

    function register($uname, $upass)
    {
        // TODO: Implement register() method.
    }

    /**
     * @param $pass string
     * @return string
     */
    function hashPass($pass)
    {
        return sha1($pass);
    }

    /**
     * @return FormInterface
     */
    public function getLoginForm()
    {
        $form = $this->formFactory->createBuilder(FormType::class);
        $form->add("song_artist", TextType::class);
        $form->add("save", SubmitType::class, array('label'=>'Save song'));

        return $form->getForm();
    }
}