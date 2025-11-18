<?php

namespace App\Controller;

use App\Entity\Jeu;
use App\Entity\User;
use App\Form\JeuType;
use App\Repository\UserRepository;
use App\Service\BanNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $jeu = new Jeu();
        $form = $this->createForm(JeuType::class, $jeu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $jeu->setUser($this->getUser());
            $entityManager->persist($jeu);
            $entityManager->flush();

            return $this->redirectToRoute('admin_dashboard');
        }

        $users = $userRepository->findAll();

        return $this->render('admin/dashboard.html.twig', [
            'form' => $form->createView(),
            'users' => $users
        ]);
    }

    #[Route('/admin/ban/{id}', name: 'admin_ban')]
    public function banUser(
        User $userToBan,
        EntityManagerInterface $em,
        BanNotificationService $banNotifier
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (in_array('ROLE_ADMIN', $userToBan->getRoles())) {
            $this->addFlash('error', "Impossible de bannir un autre administrateur.");
            return $this->redirectToRoute('admin_dashboard');
        }

        $userToBan->setIsBanned(true);
        $em->flush();

        $banNotifier->sendBanEmail($userToBan);

        $this->addFlash('success', "Utilisateur banni.");
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/unban/{id}', name: 'admin_unban')]
    public function unbanUser(
        User $userToUnban,
        EntityManagerInterface $em,
        BanNotificationService $banNotifier
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $userToUnban->setIsBanned(false);
        $em->flush();

        $banNotifier->sendUnbanEmail($userToUnban);

        $this->addFlash('success', "Utilisateur réactivé.");
        return $this->redirectToRoute('admin_dashboard');
    }
}
