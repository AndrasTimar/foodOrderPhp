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
     * @Route("/login", name="login")
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
                    if(!$user->getAddress() && !$user->getAdmin()) {
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
        if($user->getAddress()){
            return $this->redirectToRoute("foods");
        }
        return $this->redirectToRoute("address_mod");
    }

    /**
     * @Route("/register/add_admin/{adminreg}", name="add_admin", defaults={"adminreg":"true"})
     * @Route("/register", name="register")
     * @Route("/account", name="account")
     * @param $adminreg boolean
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

            $user->setAdmin($adminreg && true);
            if ($this->userService->register($user,$userId)) {
                $this->addFlash('notice', 'Success!');
                if(!$user->getAddress()){
                    return $this->redirectToRoute('login');
                }
                return $this->redirectToRoute('foods');
            } else {
                $this->addFlash('notice', 'Username already taken!');
            }

            return $this->redirectToRoute('register');
        }
        return $this->render('FoodOrder/baseform.html.twig', array("form" => $formInterface->createView(), "loggedIn" => $userId, "admin" => $user->getAdmin()));

    }

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $this->userService = $this->get("app.user_service");
    }

}