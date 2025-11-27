<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        
        if ($request->isMethod('POST')) {
            $nom = $request->request->get('nom');
            $email = $request->request->get('email');
            $message = $request->request->get('message');

            
            $emailAdmin = (new Email())
                ->from($email)
                ->to('ton@email.com') 
                ->subject('Nouveau message de contact - PartaJeux')
                ->html(
                    $this->renderView(
                        'emails/contact.html.twig',
                        [
                            'nom' => $nom,
                            'email' => $email,
                            'message' => $message
                        ]
                    )
                );

            $mailer->send($emailAdmin);

            
            $this->addFlash('success', 'Votre message a été envoyé avec succès !');
            return $this->redirectToRoute('app_contact', ['success' => 1]); 

        }

        
        return $this->render('contact/index.html.twig');
    }
}