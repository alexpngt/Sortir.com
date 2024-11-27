<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la sortie :',
                'label_attr' => ['class' => 'col-sm-4 col-form-label text-left'],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('dateStart', DateTimeType::class, [
                'label' => 'Date et heure de la sortie :',
                'widget' => 'single_text',
                'label_attr' => ['class' => 'col-sm-7 col-form-label text-left'],
                'attr' => ['class' => 'form-control col-sm-3'],
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Durée :',
                'label_attr' => ['class' => 'col-sm-9 col-form-label text-left'],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('dateLimitInscription', DateTimeType::class, [
                'label' => "Date limite d'inscription :",
                'widget' => 'single_text',
                'label_attr' => ['class' => 'col-sm-7 col-form-label text-left'],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('nbMaxInscription', IntegerType::class, [
                'label' => "Nombre de places :",
                'label_attr' => ['class' => 'col-sm-9 col-form-label text-left'],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos :',
                'label_attr' => ['class' => 'col-sm-4 col-form-label text-left'],
                'attr' => ['class' => 'form-control', 'rows' => 5],
            ])
            ->add('campusOrganisateur', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name',
                'label' => 'Campus :',
                'label_attr' => ['class' => 'col-sm-4 col-form-label text-left'],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'label' => 'Lieu :',
                'label_attr' => ['class' => 'col-sm-4 col-form-label text-left'],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('publish', SubmitType::class, [
                'label' => 'Publier la sortie',
                'attr' => ['class' => 'btn btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
