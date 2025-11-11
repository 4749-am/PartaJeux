<?php

namespace App\Controller;

use App\Entity\Jeu;
use App\Form\JeuType;
use App\Repository\JeuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user', name: 'user_dashboard')]
    public function index(Request $request, JeuRepository $jeuRepository, EntityManagerInterface $entityManager): Response
    {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    $user = $this->getUser();

    $mesJeux = $jeuRepository->findBy(['user' => $user]);
    $autresJeux = $jeuRepository->createQueryBuilder('j')
        ->where('j.user != :user')
        ->setParameter('user', $user)
        ->orderBy('j.dateSoiree', 'ASC')
        ->getQuery()
        ->getResult();

    $jeu = new Jeu();
    $form = $this->createForm(JeuType::class, $jeu);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $jeu->setUser($user);
        $jeu->addParticipant($user);
        $entityManager->persist($jeu);
        $entityManager->flush();

        return $this->redirectToRoute('user_dashboard');
    }

    return $this->render('user/dashboard.html.twig', [
        'form' => $form->createView(),
        'mesJeux' => $mesJeux,
        'autresJeux' => $autresJeux,
    ]);
    }
}
