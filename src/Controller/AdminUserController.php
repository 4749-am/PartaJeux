<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminUserController extends AbstractController
{
    #[Route('/admin/user/{id}/ban', name: 'admin_user_ban', methods: ['POST'])]
    public function ban(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('ban' . $user->getId(), $request->request->get('_token'))) {

            if ($user === $this->getUser()) {
                $this->addFlash('error', 'Vous ne pouvez pas vous bannir vous-même.');
            } else {
                $user->setIsBanned(true);
                $em->flush();
                $this->addFlash('success', 'Utilisateur banni avec succès.');
            }
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/user/{id}/unban', name: 'admin_user_unban', methods: ['POST'])]
    public function unban(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('unban' . $user->getId(), $request->request->get('_token'))) {
            $user->setIsBanned(false);
            $em->flush();
            $this->addFlash('success', 'Utilisateur débanni.');
        }

        return $this->redirectToRoute('admin_dashboard');
    }
}
