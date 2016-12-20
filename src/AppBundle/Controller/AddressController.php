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
use AppBundle\util\RequestUtil as util;
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
     * @Route("/address/list", name="address_list")
     */
    public function getList(Request $request) {

        $userId = $request->getSession()->get("userId");
        if(!$userId){
            $this->addFlash('notice', 'Please log in!');
            return $this->redirectToRoute("login");
        }
        $user = $this->userService->getUserById($userId);
        $arr = $user->getAddresses();
        $selectedId = $request->getSession()->get("address");
        $selected = null;
        if($selectedId) {
            $selected = $this->addressService->getAddressById($selectedId);
        }
        return $this->render(':FoodOrder:manageaddress.html.twig', array('addresslist'=>$arr,'selected'=>$selected,"loggedIn"=>true,"admin"=>$user->getAdmin()));
    }

    /**
     * @Route("/address/select/{addressId}", name="address_select")
     */
    public function selectAddress(Request $request, $addressId) {

        $userId = $request->getSession()->get("userId");
        if(!$userId){
            $this->addFlash('notice', 'Please log in!');
            return $this->redirectToRoute("login");
        }
        $address = $this->addressService->getAddressById($addressId);
        if($address->getUser()->getId() == $userId){
            $request->getSession()->set("address", $addressId);
        }
        return $this->redirect(util::getReferer($request));
    }

    /**
     * @Route("/address/{addressId}", name="address_mod")
     */
    public function editAddress(Request $request,$addressId = 0){


        /** @var User $user */
         $user = $this->userService->getUserById($request->getSession()->get("userId"));
        if(!$user){
            $this->addFlash('notice', 'Please log in!');
            return $this->redirectToRoute("login");
        }
        if($addressId) {
            /** @var Address $address */
            $address = $this->addressService->getAddressById($addressId);
            if($user != $address->getUser()) {
                $address = new Address();
                $address->setUser($user);
            }
        }
        else{
            $address = new Address();
            $address->setUser($user);
        }
        $formInterface = $this->addressService->getAddressForm($address);

        $formInterface->handleRequest($request);

        if ($formInterface->isSubmitted() && $formInterface->isValid())
        {
            $this->addressService->saveAddress($address);
            $this->addFlash('notice', 'Address saved!');
            return $this->redirectToRoute("address_list");
        }

        return $this->render('FoodOrder/baseform.html.twig', array("form" => $formInterface->createView(),"loggedIn"=>true,"admin"=>$user->getAdmin()));

    }

    /**
     * @Route("/address/delete/{addressId}", name="address_del")
     */
    public function deleteAddress(Request $request, $addressId = 0){
        $userId = $request->getSession()->get("userId");
        if(!$userId){
            $this->addFlash('notice', 'Please log in!');
            return $this->redirectToRoute("login");
        }
        /** @var User $user */
        $user = $this->userService->getUserById($userId);

        /** @var Address $address */
        $address = $this->addressService->getAddressById($addressId);
        if($user === $address->getUser()) {
            $this->addFlash('notice', "Address deleted!");
            $this->addressService->deleteAddress($address);
        }
        return $this->redirectToRoute("address_list");

    }

}