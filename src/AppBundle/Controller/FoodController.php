<?php
/**
 * Created by PhpStorm.
 * User: bandi
 * Date: 12/2/2016
 * Time: 13:37
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Food;
use AppBundle\Service\IFoodService;
use AppBundle\Service\IUserService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FoodController extends Controller
{
    /**
     * @var IFoodService
     */
    private $foodService;

    /**
     * @var IUserService
     */
    private $userService;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->foodService=$this->get("app.food_service");
        $this->userService=$this->get("app.user_service");
    }

    /**
     * @Route("/foods", name="foods")
     * @Route("/foods/list", name="foodlist")
     */
    public function getList(Request $request) {

        $userId = $request->getSession()->get("userId");
        if(!$userId){
            $this->addFlash('notice', 'Please log in!');
            return $this->redirectToRoute("login");
        }
        $user = $this->userService->getUserById($userId);
        $arr = $this->foodService->getAllFoods();
        return $this->render(':FoodOrder:foodlist.html.twig', array('foodlist'=>$arr,"loggedIn"=>true,"admin"=>$user->getAdmin()));
    }

    /**
     * @Route("/foods/edit/{foodId}", name="foodedit")
     */
    public function getForm($foodId=0, Request $request) {
        if(!$this->isAdmin($request)){
            $this->addFlash('notice', 'Log in as admin for this action!');
            return $this->redirectToRoute("login");
        }

        if ($foodId){
            $food = $this->foodService->getFoodById($foodId);
        } else {
            $food=new Food();
        }

        $formInterface = $this->foodService->getFoodForm($food);

        $formInterface->handleRequest($request);
        if ($formInterface->isSubmitted() && $formInterface->isValid())
        {
            $this->foodService->saveFood($food);
            $this->addFlash('notice', 'Food Saved!');
            return $this->redirectToRoute("foodlist");
        }
        $user = $this->userService->getUserById($request->getSession()->get("userId"));
        return $this->render('FoodOrder/baseform.html.twig', array("form"=>$formInterface->createView(),"loggedIn"=>true,"admin"=>$user->getAdmin()) );
    }

    /**
     * @Route("/foods/{foodId}", name="foodshow")
     */
    public function getDatasheet($foodId, Request $request){
        $userId = $request->getSession()->get("userId");
        if(!$userId){
            $this->addFlash('notice', 'Please log in!');
            return $this->redirectToRoute("login");
        }
        $food = $this->foodService->getFoodById($foodId);
        $user = $this->userService->getUserById($request->getSession()->get("userId"));
        return $this->render(':FoodOrder:foodsheet.html.twig', array("food"=>$food,"loggedIn"=>true,"admin"=>$user->getAdmin()));
    }

    /**
     * @Route("/foods/delete/{foodId}", name="fooddel")
     */
    public function delete($foodId, Request $request) {
        if(!$this->isAdmin($request)){
            $this->addFlash('notice', 'Log in as admin for this action!');
            return $this->redirectToRoute("login");
        }
        $this->foodService->deleteFood($foodId);
        $this->addFlash('notice', 'Food Deleted!');
        return $this->redirectToRoute("foodlist");
    }

    /**
     * return boolean
     */
    public function isAdmin(Request $request){
       return $this->userService->getUserById($request->getSession()->get("userId"))->getAdmin();
    }
}