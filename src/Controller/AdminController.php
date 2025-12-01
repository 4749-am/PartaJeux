<?php

namespace App\Controller;

use App\Entity\Jeu;
use App\Entity\User;
use App\Form\JeuType;
use App\Repository\JeuRepository; 
use App\Repository\UserRepository;
use App\Service\BanNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')] 
class AdminController extends AbstractController
{
    
    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        
        $jeu = new Jeu();
        $form = $this->createForm(JeuType::class, $jeu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $jeu->setUser($this->getUser());
            $entityManager->persist($jeu);
            $entityManager->flush();
            
            $this->addFlash('success', 'Nouveau jeu créé avec succès par l\'administrateur.');

            return $this->redirectToRoute('admin_dashboard');
        }

        
        return $this->render('admin/admin_dashboard.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    
    #[Route('/utilisateurs', name: 'admin_users_list')]
    public function listUsers(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $userRepository->findAll();

        return $this->render('admin/admin_users_list.html.twig', [
            'users' => $users,
        ]);
    }
    
    
    #[Route('/jeux-crees', name: 'admin_my_games')]
    public function listMyGames(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        
        $jeuxCrees = $this->getUser()->getJeux(); 

        return $this->render('admin/admin_my_games.html.twig', [
            'jeux_crees' => $jeuxCrees,
        ]);
    }

    
    #[Route('/ban/{id}', name: 'admin_user_ban')] 
    public function banUser(
        User $userToBan,
        EntityManagerInterface $em,
        BanNotificationService $banNotifier
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (in_array('ROLE_ADMIN', $userToBan->getRoles())) {
            $this->addFlash('error', "Impossible de bannir un autre administrateur.");
            return $this->redirectToRoute('admin_users_list'); 
        }

        $userToBan->setIsBanned(true);
        $em->flush();

        $banNotifier->sendBanEmail($userToBan);

        $this->addFlash('success', "Utilisateur banni.");
        return $this->redirectToRoute('admin_users_list'); 
    }

    #[Route('/unban/{id}', name: 'admin_user_unban')] 
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
        return $this->redirectToRoute('admin_users_list'); 
    }
}