<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\AddUserType;
use App\Form\UserType;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class UserController extends AbstractController
{
    public function __construct(
        private UserService $userService
    ) {}
    

    #[Route('/admin/user', name: 'admin_user_index')]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        $users = $this->userService->findUsersToIndex($page);

        $count = $this->userService->usersCount();

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
            'page' => $page,
            'total' => $count,
        ]);
    }

    #[Route('/admin/user/add', name: 'admin_user_add')]
    public function addUser(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(AddUserType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->userService->addUser($user);
            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/form.html.twig', [
            'form' => $form,
            'title' => 'Invité - Ajouter'
        ]);
    }

    #[Route('admin/user/edit', name: 'admin_user_self_edit')]
    public function selfEditUser(Request $request, #[CurrentUser]User $user): Response
    {
        $oldPassword = $user->getPassword();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->userService->updateUser($user, $oldPassword);
            return $this->redirectToRoute('admin_media_index');
        }

        return $this->render('admin/user/form.html.twig', [
            'form' => $form,
            'title' => 'Profile - Modifier'
        ]);
    }

    #[Route('admin/user/{id}/edit', name: 'admin_user_edit')]
    public function editUser(Request $request, User $user): Response
    {
        $oldPassword = $user->getPassword();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->userService->updateUser($user, $oldPassword);
            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/form.html.twig', [
            'form' => $form,
            'title' => 'Invité - Modifier'
        ]);
    }

    #[Route('admin/user/{id}/delete', name: 'admin_user_delete')]
    public function deleteUser(User $user): Response
    {
        $this->userService->deleteUserData($user);

        return $this->redirectToRoute('admin_user_index');
    }

    #[Route('admin/user/{id}/deactivate', name: 'admin_user_deactivate')]
    public function deactivateUser(User $user): Response
    {
        $this->userService->deactivateUser($user);

        return $this->redirectToRoute('admin_user_index');
    }

    #[Route('admin/user/{id}/activate', name: 'admin_user_activate')]
    public function activateUser(User $user): Response
    {
        $this->userService->activateUser($user);
        
        return $this->redirectToRoute('admin_user_index');
    }
}
