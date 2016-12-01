<?php
/**
 * Created by PhpStorm.
 * User: bandi
 * Date: 11/30/2016
 * Time: 16:46
 */

namespace AppBundle\Controller;


use AppBundle\Service\IAuthenticationService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthenticationController extends Controller
{

    /**
     * @var IAuthenticationService
     */
    private $authenticationService;

    /**
     * @Route("/login", name="login")
     */
    public function indexAction(Request $request)
    {
        $username = $request->request->get("username");
        $password = $request->request->get("password");
        if($username && $password){
            if($this->authenticationService->login($username,$password)){
                echo "OK";
            }else{
                echo "NEMOK";
            }
        }
        else
        {
            $response = $this->render('FoodOrder/login.html.twig');
            return $response;
        }

        return new Response();
    }


    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->authenticationService=$this->get("app.authenticationservice");
    }

}