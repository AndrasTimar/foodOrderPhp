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
use AppBundle\Service\PasswordEncoderService;
use AppBundle\util\RequestUtil;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{



    /**
     * @var PasswordEncoderService
     */
    private $passwordEncoder;
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
        return $this->redirectToRoute("fos_user_security_logout");
    }

    /**
     * @Route("/account/delete", name="account_del")
     */
    public function accountDel(Request $request){
        try {
            $user = $this->getUser();
            $this->userService->deleteUser($user);
        }
        catch(\Exception $ex){
            $this->addFlash('notice', $ex->getMessage());
        }
        $this->addFlash('notice', 'Account Deleted!');
        return $this->redirectToRoute("logout");

    }

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->passwordEncoder = $this->get('app.password_encoder');
        $this->userService = $this->get("app.user_service");
    }


    /**
     * @Route("/users", name="users")
     * @Route("/users/admin/list", name="userlist")
     */
    public function getList(Request $request) {

        $users = $this->userService->getAllUsers();
        return $this->render(':FoodOrder:userlist.html.twig', array('userlist'=>$users));
    }

    /**
     * @Route("/users/admin/delete/{userId}", name="userdel_admin")
     */
    public function delete($userId, Request $request) {
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
     * @Route("/users/admin/edit/{userId}", name="useredit_admin")
     */
    public function edit(Request $request, $userId)
    {
        $user = $this->userService->getUserById($userId);
        $formInterface = $this->userService->getAdminEditForm($user);
        $formInterface->handleRequest($request);

        if ($formInterface->isSubmitted() && $formInterface->isValid()) {
            $this->addUserAndRedirect($user,RequestUtil::getReferer($request));
            return $this->redirectToRoute("userlist");
        }
        return $this->render('FoodOrder/baseform.html.twig', array("form" => $formInterface->createView()));
    }

    /**
     * @param $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addUserAndRedirect($user, $referer)
    {
        try{
        $this->userService->persistUser($user);
            $this->addFlash('notice', 'Success!');
            return $this->redirectToRoute('foods');
        }catch (UniqueConstraintViolationException $ex){
            $this->addFlash('notice', 'Username taken!');
            return $this->redirect($referer);
        }catch (NotNullConstraintViolationException $ex){
            $this->addFlash('notice', 'Invalid input!');
            return $this->redirect($referer);
        }catch (\Exception $ex){
            $this->addFlash('notice', 'Unknown Error!');
            return $this->redirect($referer);
        }
    }
    /**
     * @Route("/users/admin/promote/{userId}", name="promote")
     */
    public function promoteToAdmin(Request $request,$userId){
        $this->userService->promoteToAdmin($userId);
        return $this->redirect(RequestUtil::getReferer($request));
    }
    /**
     * @Route("/users/admin/demote/{userId}", name="demote")
     */
    public function demoteToUser(Request $request,$userId){
        $this->userService->demoteToUser($userId);
        return $this->redirect(RequestUtil::getReferer($request));
    }
}