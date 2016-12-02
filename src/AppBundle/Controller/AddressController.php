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
use AppBundle\Service\AddressService;
use AppBundle\Service\IAddressService;
use AppBundle\Service\IUserService;
use AppBundle\Service\UserService;
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

        $userName = $request->getSession()->get("user");
        if(!$userName){ 
            return $this->redirectToRoute("login");
        }
        $user = $this->userService->getUserByName($userName);
        $addressId = $user->getAddress();

        if(!$addressId){
            $address = new Address();
        }else{
            $address = $this->addressService->getAddressById($addressId);
        }
        $formInterface = $this->addressService->getAddressForm($address);

        $formInterface->handleRequest($request);
        // AUTOMATIC CSRF DETECTION!!!
        if ($formInterface->isSubmitted() && $formInterface->isValid())
        {
            $this->addressService->saveAddress($address);
            $this->userService->updateAddress($address, $user);
            $this->addFlash('notice', 'Address SAVED!');
            return $this->redirectToRoute("address_mod");
        }

        return $this->render('FoodOrder/baseform.html.twig', array("form" => $formInterface->createView(),"loggedIn"=>true));


    }

}