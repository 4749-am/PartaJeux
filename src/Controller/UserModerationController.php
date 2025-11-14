<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class UserModerationController extends AbstractController
{
    #[Route('/user/ban/{id}', name: 'user_ban')]
    public function ban(User $targetUser, EntityManagerInterface $em): RedirectResponse
    {
        $current = $this->getUser();

        if (!$current) {
            throw $this->createAccessDeniedException();
        }

        if ($current->getId() === $targetUser->getId()) {
            $this->addFlash('error', "Vous ne pouvez pas vous bannir vous-même.");
            return $this->redirectToRoute('user_dashboard');
        }

        if (in_array('ROLE_ADMIN', $targetUser->getRoles())) {
            $this->addFlash('error', "Impossible de bannir un administrateur.");
            return $this->redirectToRoute('user_dashboard');
        }

        $targetUser->setIsBanned(true);
        $em->flush();

        $this->addFlash('success', "{$targetUser->getUsername()} a été banni.");
        return $this->redirectToRoute('user_dashboard');
    }

    #[Route('/user/unban/{id}', name: 'user_unban')]
    public function unban(User $targetUser, EntityManagerInterface $em): RedirectResponse
    {
        $current = $this->getUser();

        if (!$current) {
            throw $this->createAccessDeniedException();
        }

        if (in_array('ROLE_ADMIN', $targetUser->getRoles())) {
            $this->addFlash('error', "Impossible de débannir un administrateur.");
            return $this->redirectToRoute('user_dashboard');
        }

        $targetUser->setIsBanned(false);
        $em->flush();

        $this->addFlash('success', "{$targetUser->getUsername()} a été débanni.");
        return $this->redirectToRoute('user_dashboard');
    }
}
