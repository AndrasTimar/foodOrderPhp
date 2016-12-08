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
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var FormFactory
     */
    private $formFactory;
    /**
     * @var UserRepository
     */
    private $userRepo;

    /**
     * @var PasswordEncoderService
     */
    private $passwordEncoder;
    /**
     * AuthenticationService constructor.
     * @param $entityManager EntityManager
     * @param $formFactory FormFactory
     */
    public function __construct(EntityManager $entityManager,FormFactory $formFactory, PasswordEncoderService $passwordEncoderService)
    {
        $this->entityManager = $entityManager;
        $this->userRepo = $entityManager->getRepository(User::class);
        $this->formFactory = $formFactory;
        $this->passwordEncoder=$passwordEncoderService;
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
     * @return User
     */
    function login($uname, $upass)
    {
        $upass = $this->passwordEncoder->hashPass($upass);
        return $this->userRepo->findByNameAndPassword($uname,$upass);

    }

    /**
     * @param $user User
     * @return boolean
     */
    function register($user, $userId)
    {
        $user->setPassword($this->passwordEncoder->hashPass($user->getPlainPassword()));

        $found = false;

        if(!$userId) {
            $found = $this->userRepo->findBy(["username" => $user->getUsername()]);
        }
        if($userId || !$found) {
            $this->entityManager->merge($user);
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
        $form->add("register", SubmitType::class, array('label'=>'Save'));
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
     * @param $userId integer
     * @return User
     */
    public function getUserById($userId){
        return $this->userRepo->find($userId);
    }

    /**
     * @param $address Address
     * @param $user User
     */
    public function updateAddress($address, $user)
    {
        $user->setAddress($address);
        $queryBuilder = $this->userRepo->createQueryBuilder("u");
        $queryBuilder->update()
            ->set("u.address",":addid")
            ->where("u.id = :uid")
            ->setParameter('addid',$address->getId())
            ->setParameter("uid",$user->getId())
            ->getQuery()
            ->execute();
    }
}