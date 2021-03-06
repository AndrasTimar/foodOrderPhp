<?php

namespace  AppBundle\Service;
use AppBundle\AppBundle;
use AppBundle\DTO\UserDTO;
use AppBundle\Entity\Address;
use AppBundle\Entity\Group;
use AppBundle\Entity\User;
use AppBundle\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
     * @param $user User
     * @return FormInterface
     */
    public function getRegForm($user)
    {
        $form = $this->formFactory->createBuilder(FormType::class, $user);
        $form->add("username", TextType::class);
        $form->add("plainPassword", PasswordType::class, array('label'=>'Password'));
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
     * @param $user User
     */
    public function deleteUser($user)
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    /**
     * @return User[]
     */
    public function getAllUsers()
    {
       return $this->userRepo->findAll();
    }

    /**
     * @param $user User
     * @return FormInterface
     */
    public function getAdminEditForm($user)
    {
        $form = $this->formFactory->createBuilder(FormType::class, $user);
        $form->add("username", TextType::class);
        $form->add("email", EmailType::class);
        $form->add("register", SubmitType::class, array('label'=>'Save'));
        return $form->getForm();
    }

    public function promoteToAdmin($userId)
    {
        /** @var User $user */
        $user = $this->getUserById($userId);
        $user->addRole('ROLE_ADMIN');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }


    public function persistUser($user)
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;
    }

    public function demoteToUser($userId)
    {
        $user = $this->getUserById($userId);
        $user->removeRole('ROLE_ADMIN');
        $user->addRole('ROLE_USER');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}