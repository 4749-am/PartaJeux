<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\User;

class BanNotificationService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendBanEmail(User $user): void
    {
        if (!$user->getEmail()) {
            return; 
        }

        $email = (new Email())
            ->from('no-reply@partajeux.com')
            ->to($user->getEmail())
            ->subject('Votre compte a été banni')
            ->html("
                <p>Bonjour <strong>{$user->getUsername()}</strong>,</p>
                <p>Nous vous informons que votre compte a été <strong>banni</strong> de la plateforme PartaJeux.</p>
                <p>Si vous pensez qu'il s'agit d'une erreur, vous pouvez contacter l’administrateur.</p>
                <br>
                <p>L’équipe PartaJeux</p>
            ");

        $this->mailer->send($email);
    }

    public function sendUnbanEmail(User $user): void
    {
        if (!$user->getEmail()) {
            return;
        }

        $email = (new Email())
            ->from('no-reply@partajeux.com')
            ->to($user->getEmail())
            ->subject('Votre compte est à nouveau actif')
            ->html("
                <p>Bonjour <strong>{$user->getUsername()}</strong>,</p>
                <p>Votre compte a été <strong>réactivé</strong> sur la plateforme PartaJeux.</p>
                <p>Vous pouvez désormais vous reconnecter et utiliser toutes les fonctionnalités.</p>
                <br>
                <p>L’équipe PartaJeux</p>
            ");

        $this->mailer->send($email);
    }
}
