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

class UserController extends AbstractController
{
    
    #[Route('/user', name: 'user_dashboard')]
    public function dashboard(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        
        $jeu = new Jeu();
        $form = $this->createForm(JeuType::class, $jeu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $jeu->setUser($user);
            $jeu->addParticipant($user);
            $entityManager->persist($jeu);
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre jeu a été créé avec succès !');

            return $this->redirectToRoute('user_games');
        }

        return $this->render('user/user_dashboard.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    
    #[Route('/user/mes-jeux', name: 'user_games')]
    public function userGames(JeuRepository $jeuRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        
        $mesJeux = $jeuRepository->findBy(['user' => $user], ['dateSoiree' => 'ASC']);

        return $this->render('user/user_games.html.twig', [
            'jeux_crees' => $mesJeux,
        ]);
    }

    
    #[Route('/user/jeux-disponibles', name: 'available_games')]
    public function availableGames(JeuRepository $jeuRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        
        $autresJeux = $jeuRepository->createQueryBuilder('j')
            ->where('j.user != :user')
            ->setParameter('user', $user)
            ->orderBy('j.dateSoiree', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('user/available_games.html.twig', [
            'autresJeux' => $autresJeux,
        ]);
    }

    
    #[Route('/user/liste', name: 'user_list')]
    public function userList(UserRepository $userRepo): Response
    {
        
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $utilisateurs = $userRepo->findAll();

        return $this->render('user/user_list.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
    }

    
    #[Route('/user/ban/{id}', name: 'user_ban')]
    public function banUser(
        User $userToBan,
        EntityManagerInterface $em,
        BanNotificationService $banNotifier
    ): Response {
        $currentUser = $this->getUser();

        if (!$currentUser) { 
            throw $this->createAccessDeniedException();
        }

        if (in_array('ROLE_ADMIN', $userToBan->getRoles())) {
            $this->addFlash('error', "Impossible de bannir un admin.");
            return $this->redirectToRoute('user_list');
        }

        if ($userToBan === $currentUser) {
            $this->addFlash('error', "Tu ne peux pas te bannir toi-même.");
            return $this->redirectToRoute('user_list');
        }

        $userToBan->setIsBanned(true);
        $em->flush();

        $banNotifier->sendBanEmail($userToBan);

        $this->addFlash('success', "Utilisateur banni.");
        return $this->redirectToRoute('user_list');
    }

   
    #[Route('/user/unban/{id}', name: 'user_unban')]
    public function unbanUser(
        User $userToUnban,
        EntityManagerInterface $em,
        BanNotificationService $banNotifier
    ): Response {
        $currentUser = $this->getUser();

        if (!$currentUser) { 
            throw $this->createAccessDeniedException();
        }

        if (in_array('ROLE_ADMIN', $userToUnban->getRoles())) {
            $this->addFlash('error', "Impossible d’agir sur un admin.");
            return $this->redirectToRoute('user_list');
        }

        $userToUnban->setIsBanned(false);
        $em->flush();

        $banNotifier->sendUnbanEmail($userToUnban);

        $this->addFlash('success', "Utilisateur débanni.");
        return $this->redirectToRoute('user_list');
    }
}