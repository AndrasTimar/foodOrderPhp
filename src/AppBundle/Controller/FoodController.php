<?php
/**
 * Created by PhpStorm.
 * User: bandi
 * Date: 12/2/2016
 * Time: 13:37
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Food;
use AppBundle\Entity\User;
use AppBundle\Service\IFoodService;
use AppBundle\Service\IUserService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
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
     * @Route("/", name="base")
     * @Route("/foods/view", name="foods")
     * @Route("/foods/list", name="foodlist")
     */
    public function getList(Request $request) {
        $this->userService->promoteToAdmin($this->getUser());
        $arr = $this->foodService->getAllFoods();
        return $this->render(':FoodOrder:foodlist.html.twig', ["foodlist"=>$arr]);
   }

    /**
     * @Route("/foods/admin/edit/{foodId}", name="foodedit")
     */
    public function getForm($foodId=0, Request $request) {

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
        return $this->render('FoodOrder/baseform.html.twig', array("form"=>$formInterface->createView()) );
    }

    /**
     * @Route("/foods/{foodId}", name="foodshow")
     */
    public function getDatasheet($foodId, Request $request){
        $food = $this->foodService->getFoodById($foodId);
        return $this->render(':FoodOrder:foodsheet.html.twig', array("food"=>$food));
    }

    /**
     * @Route("/foods/admin/delete/{foodId}", name="fooddel")
     */
    public function delete($foodId, Request $request) {
        try {
            $this->foodService->deleteFood($foodId);
            $this->addFlash('notice', 'Food Deleted!');
            return $this->redirectToRoute("foodlist");
        }catch (\Exception $exception){
            $this->addFlash('notice', $exception->getMessage());
            return $this->redirectToRoute("foodlist");
        }

    }
}