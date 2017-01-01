<?php
/**
 * Created by PhpStorm.
 * User: bandi
 * Date: 11/30/2016
 * Time: 16:46
 */

namespace AppBundle\Controller;


use AppBundle\DTO\UserDTO;
use AppBundle\Entity\User;
use AppBundle\Service\IUserService;
use AppBundle\util\RequestUtil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{

    /**
     * @var IUserService
     */
    private $userService;
    /**
     * @Route("/logout", name="logout")
     */
    public function logout(Request $request)
    {
        $request->getSession()->clear();
        return $this->redirectToRoute("login");
    }

    /**
     * @Route("/account/delete", name="account_del")
     */
    public function accountDel(Request $request){
        try {
            $userId = $request->getSession()->get("userId");
            $user = $this->userService->getUserById($userId);
            $this->userService->deleteUser($user);
        }
        catch(\Exception $ex){
            $this->addFlash('notice', $ex->getMessage());
        }
        $this->addFlash('notice', 'Account Deleted!');
        return $this->redirectToRoute("logout");

    }
    /**
     * @Route("/login", name="login")
     * @Route("/", name="base")
     */
    public function login(Request $request)
    {
        $userId = $request->getSession()->get("userId");
        if (!$userId) {
            $userDTO = new UserDTO();
            $formInterface = $this->userService->getLoginForm($userDTO);
            $formInterface->handleRequest($request);

            if ($formInterface->isSubmitted() && $formInterface->isValid()) {
                $username = $userDTO->username;
                $password = $userDTO->password;
                $user = $this->userService->login($username, $password);
                if ($user) {
                    $request->getSession()->set("userId", $user->getId());
                    $request->getSession()->set("orderItems", array());
                    $this->addFlash('notice', 'Login Successful! Welcome, ' . $user->getUsername());
                    if(!$user->getAddresses() && !$user->getAdmin()) {
                        return $this->redirectToRoute("address_mod");
                    }
                    return $this->redirectToRoute("foods");

                }

                $this->addFlash('notice', 'Invalid Credentials!');
                return $this->redirectToRoute("login");
            }

            return $this->render('FoodOrder/baseform.html.twig', array("form" => $formInterface->createView(), "loggedIn" => false, "admin" => false));
        }
        $user = $this->userService->getUserById($userId);
        if($user->getAddresses()){
            return $this->redirectToRoute("foods");
        }
        return $this->redirectToRoute("address_mod");
    }

    /**
     * @Route("/register/add_admin/{adminreg}", name="add_admin", defaults={"adminreg":"true"})
     * @Route("/register", name="register")
     * @Route("/account", name="account")
     * @param $adminreg boolean
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function register(Request $request, $adminreg = false)
    {
        $userId = $request->getSession()->get("userId");

        if($userId){
            $user = $this->userService->getUserById($userId);
        }
        else{
            $user = new User();
        }
        if($adminreg && !$user->getAdmin()){
            return $this->redirectToRoute('register');
        }else if($adminreg){
            $user = new User();
        }
        $formInterface = $this->userService->getRegForm($user);

        $formInterface->handleRequest($request);

        if ($formInterface->isSubmitted() && $formInterface->isValid()) {

            $user->setAdmin($adminreg ? 1 : 0);
            if ($this->userService->register($user,$userId)) {
                $this->addFlash('notice', 'Success!');
                if(!$user->getAddresses()){
                    return $this->redirectToRoute('login');
                }
                return $this->redirectToRoute('foods');
            } else {
                $this->addFlash('notice', 'Username already taken!');
            }

            return $this->redirectToRoute('register');
        }
        if(!$userId) {
            return $this->render('FoodOrder/baseform.html.twig', array("form" => $formInterface->createView(), "loggedIn" => $userId, "admin" => $this->userService->getUserById($userId)));
        }
        return $this->render('FoodOrder/accountsettings.html.twig', array("form" => $formInterface->createView(), "loggedIn" => $userId, "admin" => $this->userService->getUserById($userId)));

    }

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $this->userService = $this->get("app.user_service");
    }


    /**
     * @Route("/users", name="users")
     * @Route("/users/list", name="userlist")
     */
    public function getList(Request $request) {

        $userId = $request->getSession()->get("userId");
        if(!$userId){
            $this->addFlash('notice', 'Please log in!');
            return $this->redirectToRoute("login");
        }
        $user = $this->userService->getUserById($userId);
        if(!$user->getAdmin()) {
            $this->addFlash('notice', 'You have to be an admin for this action');
            return $this->redirectToRoute('login');
        }
        $users = $this->userService->getAllUsers();

        return $this->render(':FoodOrder:userlist.html.twig', array('userlist'=>$users,"loggedIn"=>true,"admin"=>$user->getAdmin()));
    }

    /**
     * @Route("/users/delete/{userId}", name="userdel_admin")
     */
    public function delete($userId, Request $request) {
        $currentUser = $this->userService->getUserById($request->getSession()->get("userId"));
        if(!$currentUser->getAdmin()){
            $this->addFlash('notice', 'Log in as admin for this action!');
            return $this->redirectToRoute("login");
        }
        try {
            $user = $this->userService->getUserById($userId);
            $this->userService->deleteUser($user);
            $this->addFlash('notice', 'User Deleted!');
            return $this->redirectToRoute("userlist");
        }catch (\Exception $exception){
            $this->addFlash('notice', $exception->getMessage());
            return $this->redirectToRoute("userlist");
        }

    }

    /**
     * @Route("/users/edit/{userId}", name="useredit_admin")
     */
    public function edit(Request $request, $userId)
    {
        if (!$this->userService->getUserById($request->getSession()->get("userId"))->getAdmin()) {
            $this->addFlash('notice', 'Log in as admin for this action!');
            return $this->redirectToRoute("login");
        }
        $user = $this->userService->getUserById($userId);
        $formInterface = $this->userService->getAdminEditForm($user);
        $formInterface->handleRequest($request);

        if ($formInterface->isSubmitted() && $formInterface->isValid()) {
            if ($this->userService->register($user,$userId)) {
                $this->addFlash('notice', 'Success!');
            }
            return $this->redirectToRoute("userlist");
        }
        return $this->render('FoodOrder/baseform.html.twig', array("form" => $formInterface->createView(), "loggedIn" => $userId, "admin" => $user->getAdmin()));
    }
}