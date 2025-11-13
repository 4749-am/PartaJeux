<?php

namespace App\Controller;

use App\Entity\Jeu;
use App\Form\JeuType;
use App\Repository\JeuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/game')]
final class GameController extends AbstractController
{
    #[Route(name: 'app_game_index', methods: ['GET'])]
    public function index(JeuRepository $jeuRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('game/index.html.twig', [
            'jeux' => $jeuRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_game_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $jeu = new Jeu();
        $form = $this->createForm(JeuType::class, $jeu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $jeu->setUser($this->getUser());
            $jeu->addParticipant($this->getUser());
            $entityManager->persist($jeu);
            $entityManager->flush();

            return $this->isGranted('ROLE_ADMIN')
                ? $this->redirectToRoute('app_game_index')
                : $this->redirectToRoute('user_dashboard');
        }

        return $this->render('game/new.html.twig', [
            'jeu' => $jeu,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_game_show', methods: ['GET'])]
    public function show(Jeu $jeu): Response
    {
        return $this->render('game/show.html.twig', ['jeu' => $jeu]);
    }

    #[Route('/{id}/edit', name: 'app_game_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Jeu $jeu, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $jeu->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres jeux.');
        }

        $form = $this->createForm(JeuType::class, $jeu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->isGranted('ROLE_ADMIN')
                ? $this->redirectToRoute('app_game_index')
                : $this->redirectToRoute('user_dashboard');
        }

        return $this->render('game/edit.html.twig', ['jeu' => $jeu, 'form' => $form]);
    }

    #[Route('/{id}', name: 'app_game_delete', methods: ['POST'])]
    public function delete(Request $request, Jeu $jeu, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$jeu->getId(), $request->get('_token'))) {
            if ($this->isGranted('ROLE_ADMIN') || $jeu->getUser() === $this->getUser()) {
                $entityManager->remove($jeu);
                $entityManager->flush();
            } else {
                throw $this->createAccessDeniedException('Suppression non autorisée.');
            }
        }

        return $this->isGranted('ROLE_ADMIN')
            ? $this->redirectToRoute('app_game_index')
            : $this->redirectToRoute('user_dashboard');
    }

    #[Route('/{id}/join', name: 'app_game_join', methods: ['POST'])]
    public function join(Request $request, Jeu $jeu, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isCsrfTokenValid('join' . $jeu->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_home');
        }

        if ($jeu->getParticipants()->contains($user)) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cette soirée.');
        } elseif ($jeu->getNombrePlacesRestantes() <= 0) {
            $this->addFlash('error', 'Aucune place disponible pour cette soirée.');
        } else {
            $jeu->addParticipant($user);
            $em->flush();
            $this->addFlash('success', 'Inscription réussie !');
        }

        return $this->redirectToRoute('app_game_show', ['id' => $jeu->getId()]);
    }

    #[Route('/{id}/leave', name: 'app_game_leave', methods: ['POST'])]
    public function leave(Request $request, Jeu $jeu, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isCsrfTokenValid('leave' . $jeu->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_game_show', ['id' => $jeu->getId()]);
        }

        if (!$jeu->getParticipants()->contains($user)) {
            $this->addFlash('warning', 'Vous n’êtes pas inscrit à cette soirée.');
        } else {
            if ($jeu->getUser() === $user) {
                $this->addFlash('error', 'Vous êtes l’organisateur de cette soirée et ne pouvez pas vous désinscrire.');
            } else {
                $jeu->removeParticipant($user);
                $em->flush();
                $this->addFlash('success', 'Vous vous êtes désinscrit avec succès.');
            }
        }

        return $this->redirectToRoute('app_game_show', ['id' => $jeu->getId()]);
    }
}
