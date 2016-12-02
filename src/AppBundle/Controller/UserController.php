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
use AppBundle\Service\IPasswordEncoderService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{

    /**
     * @var IUserService
     */
    private $userService;

    /**
     * @var IPasswordEncoderService
     */
    private $passwordEncoder;

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(Request $request){
        $request->getSession()->clear();
        return $this->redirectToRoute("login");
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request)
    {
        if(!$request->getSession()->get("user")) {
            $userDTO = new UserDTO();
            $formInterface = $this->userService->getLoginForm($userDTO);
            $formInterface->handleRequest($request);
            if ($formInterface->isSubmitted() && $formInterface->isValid()) {
                $username = $userDTO->username;
                $password = $userDTO->password;
                $user = $this->userService->login($username, $password);
                if ($user) {
                    $request->getSession()->set("user", $username);
                    $request->getSession()->set("admin",$user->getAdmin());
                    $this->addFlash('notice', 'Login Successful! Welcome, ' . $request->getSession()->get("user"));
                    return $this->redirectToRoute("address_mod");
                }
                $this->addFlash('notice', 'Invalid Credentials!');
                return $this->redirectToRoute("login");
            }
            return $this->render('FoodOrder/baseform.html.twig', array("form" => $formInterface->createView(),"loggedIn"=>false,"admin"=>$request->getSession()->get("admin")));
        }

        return $this->redirectToRoute("address_mod");
    }

    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, $adminreg = false)
    {
        $user = new User();
        $formInterface = $this->userService->getRegForm($user);

        $formInterface->handleRequest($request);
        if ($formInterface->isSubmitted() && $formInterface->isValid()) {
            $user->setAdmin($adminreg);
            if($this->userService->register($user)){
                $this->addFlash('notice', 'Success, please log in!');
                return $this->redirectToRoute('login');
            }else{
                $this->addFlash('notice', 'Username already taken!');
            }

            return $this->redirectToRoute('register');
        }

        return $this->render('FoodOrder/baseform.html.twig', array("form"=>$formInterface->createView(),"loggedIn"=>false,"admin"=>$request->getSession()->get("admin")) );

    }

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->userService=$this->get("app.user_service");
        $this->passwordEncoder = $this->get('app.password_encoder');
    }

}