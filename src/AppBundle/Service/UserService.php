<?php

namespace  AppBundle\Service;
use AppBundle\AppBundle;
use AppBundle\DTO\UserDTO;
use AppBundle\Entity\Address;
use AppBundle\Entity\User;
use AppBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;

/**
 * Created by PhpStorm.
 * User: bandi
 * Date: 11/30/2016
 * Time: 16:52
 */
class UserService implements IUserService
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
     * @param $userDTO UserDTO
     * @return FormInterface
     */
    function getLoginForm($userDTO){
        $form = $this->formFactory->createBuilder(FormType::class, $userDTO);
        $form->add("username", TextType::class);
        $form->add("password", PasswordType::class);
        $form->add("login", SubmitType::class, array('label'=>'Login'));
        return $form->getForm();
    }

    /**
     * @param $uname string
     * @param $upass string
     * @return boolean
     */
    function login($uname, $upass)
    {
        $found = $this->userRepo->findByNameAndPassword($uname,$upass);
        if($found){
            return true;
        }
        return false;
    }

    /**
     * @param $user User
     * @return boolean
     */
    function register($user)
    {
        $found = $this->userRepo->findBy(["username"=>$user->getUsername()]);
        if(!$found) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return true;
        }

        return false;
    }

    /**
     * @param $user User
     * @return FormInterface
     */
    public function getRegForm($user)
    {
        $form = $this->formFactory->createBuilder(FormType::class, $user);
        $form->add("username", TextType::class);
        $form->add("plainPassword", PasswordType::class);
        $form->add("email", EmailType::class);
        $form->add("realName", TextType::class);
        $form->add("register", SubmitType::class, array('label'=>'Register'));
        return $form->getForm();
    }

    /**
     * @param $username String
     * @return User
     */
    public function getUserByName($username){
        return $this->userRepo->findOneBy(["username"=>$username]);
    }

    /**
     * @param $address Address
     * @param $user User
     */
    public function updateAddress($address, $user)
    {
        $user->setAddress($address);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}