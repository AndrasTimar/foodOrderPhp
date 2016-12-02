<?php
/**
 * Created by PhpStorm.
 * User: bandi
 * Date: 12/2/2016
 * Time: 00:29
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Address;
use AppBundle\Entity\User;
use AppBundle\Service\IAddressService;
use AppBundle\Service\IUserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class AddressController extends Controller
{
    /**
     * @var IAddressService
     */
    private $addressService;

    /**
     * @var IUserService
     */
    private $userService;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->userService=$this->get("app.user_service");
        $this->addressService=$this->get("app.address_service");
    }
    /**
     * @Route("/address", name="address_mod")
     */
    public function editAddress(Request $request){

        /** @var User $user */
        $user = $user = $this->userService->getUserById($request->getSession()->get("userId"));
        if(!$user){
                $this->addFlash('notice', 'Please log in!');
                return $this->redirectToRoute("login");
        }
        /** @var Address $address */
        $address = $user->getAddress();

        if(!$address){
            $address = new Address();
        }

        $formInterface = $this->addressService->getAddressForm($address);

        $formInterface->handleRequest($request);

        if ($formInterface->isSubmitted() && $formInterface->isValid())
        {
            $this->addressService->saveAddress($address);
            $this->userService->updateAddress($address, $user);
            $this->addFlash('notice', 'Address SAVED!');
            return $this->redirectToRoute("foods");
        }

        return $this->render('FoodOrder/baseform.html.twig', array("form" => $formInterface->createView(),"loggedIn"=>true,"admin"=>$user->getAdmin()));


    }

}