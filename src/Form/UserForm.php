<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Image;


class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Pseudo',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le pseudo est obligatoire']),
                    new Length([
                        'min' => 3,
                        'minMessage' => "Le pseudo doit comporter au moins {{ limit }} caractères.",
                        'max' => 180 ]),
                ],
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le prénom est obligatoire']),
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire']),

                ],
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est obligatoire']),
                    new Email(['message' => 'Veuillez entrer un email valide']),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false, // Champ non mappé à l'entité (sera hashé avant sauvegarde)
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le mot de passe est obligatoire']),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
            ])

            ->add('confirm_password', PasswordType::class, [
                'label' => 'Confirmation',
                'mapped' => false, // Champ non mappé à l'entité
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'La confirmation du mot de passe est obligatoire']),
                    new EqualTo([
                        'propertyPath' => 'password',
                        'message' => 'Les mots de passe doivent être identiques',
                    ]),
                ],
            ])

            ->add('campus', EntityType::class, [
                'label' => 'Campus',
                'class' => Campus::class,
                'choice_label' => 'name', // attr de l'entité Campus
                'required' => true,
                // Définit un texte par défaut dans le menu déroulant pour guider l'utilisateur avant qu'il ne fasse une sélection
                'placeholder' => '--Choisir un campus--',
                // désactive la sélection multiple, permettant à l'utilisateur 1 seul campus
                'multiple' => false,
                // Pour trier la liste de campus, on utilise un queryBuilder spécifique dans le formulaire
                'query_builder' => function (EntityRepository $er) {
                    // Crée un QueryBuilder pour construire une requête pour récupérer les campus
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
            ])

            ->add('photo', FileType::class, [
                'label' => 'Ma photo',
                'required' => false, // Photo facultative
                'mapped' => false, // Non mappé directement à l'entité
                'constraints' => [
                    new Image([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Merci de télécharger une photo de profil valide',
                    ])
                ],
            ]);
        // Événement pour ajouter un champ conditionnel si une image est déjà présente
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $user = $event->getData();
            if ($user && $user->getFilename()) {
                // cas où on est en modification et qu'une image est déja présente
                // on ajoute un checkbox pour permettre de dder la suppression de l'image
                $form = $event->getForm();
                $form->add('deleteImage', CheckboxType::class, [
                    'required' => false,
                    'mapped' => false,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
