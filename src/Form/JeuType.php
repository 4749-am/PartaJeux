<?php

namespace App\Form;

use App\Entity\Jeu;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JeuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', null, [
                'label' => 'Nom du jeu',
                'attr' => ['class' => 'cyber-input'],
            ])
            ->add('description', null, [
                'label' => 'Description',
                'attr' => ['class' => 'cyber-input'],
            ])
            ->add('ville', null, [
                'label' => 'Ville',
                'attr' => ['class' => 'cyber-input'],
            ])
            ->add('latitude', null, [
                'label' => 'Latitude',
                'attr' => ['class' => 'cyber-input'],
            ])
            ->add('longitude', null, [
                'label' => 'Longitude',
                'attr' => ['class' => 'cyber-input'],
            ])
            ->add('dateSoiree', DateTimeType::class, [
                'label' => 'Date et heure de la soirée',
                'widget' => 'single_text',
                'attr' => ['class' => 'cyber-input'],
            ])
            ->add('nombrePlaces', IntegerType::class, [
                'label' => 'Nombre de places',
                'attr' => ['class' => 'cyber-input'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Jeu::class,
        ]);
    }
}
