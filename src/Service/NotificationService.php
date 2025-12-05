<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment; 
use App\Entity\User;
use App\Entity\Jeu;


class NotificationService
{
    private MailerInterface $mailer;
    private Environment $twig;

    
    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendBanEmail(User $user): void
    {
        if (!$user->getEmail()) {
            return; 
        }

        
        $htmlContent = $this->twig->render('emails/ban_notification.html.twig', [
            'user' => $user,
        ]);

        $email = (new Email())
            ->from('no-reply@partajeux.com')
            ->to($user->getEmail())
            ->subject('Votre compte a été banni')
            ->html($htmlContent);

        $this->mailer->send($email);
    }

    public function sendUnbanEmail(User $user): void
    {
        if (!$user->getEmail()) {
            return;
        }

        
        $htmlContent = $this->twig->render('emails/unban_notification.html.twig', [
            'user' => $user,
        ]);

        $email = (new Email())
            ->from('no-reply@partajeux.com')
            ->to($user->getEmail())
            ->subject('Votre compte est à nouveau actif')
            ->html($htmlContent);

        $this->mailer->send($email);
    }

    
    public function sendGameUpdateNotification(Jeu $jeu, array $participants): void
    {
       
        $recipients = [];
        foreach ($participants as $user) {
            
            if ($user->getEmail() && $user !== $jeu->getUser()) {
                $recipients[] = $user->getEmail();
            }
        }

        if (empty($recipients)) {
            return; 
        }

        
        $htmlContent = $this->twig->render('emails/game_update_notification.html.twig', [
            'jeu' => $jeu,
        ]);

        
        $email = (new Email())
            ->from('no-reply@partajeux.com')
            ->bcc(...$recipients) 
            ->subject('Mise à jour importante concernant la partie "' . $jeu->getTitre() . '"')
            ->html($htmlContent);

        $this->mailer->send($email);
    }

    
    public function sendGameDeletionNotification(Jeu $jeu, array $participants): void
    {
        $recipients = [];
        foreach ($participants as $user) {
            
            if ($user->getEmail() && $user !== $jeu->getUser()) {
                $recipients[] = $user->getEmail();
            }
        }

        if (empty($recipients)) {
            return;
        }

        $htmlContent = $this->twig->render('emails/game_deletion_notification.html.twig', [
            'jeu' => $jeu,
        ]);

        $email = (new Email())
            ->from('no-reply@partajeux.com')
            ->bcc(...$recipients)
            ->subject('Annulation : La partie "' . $jeu->getTitre() . '" a été annulée')
            ->html($htmlContent);

        $this->mailer->send($email);
    }
}