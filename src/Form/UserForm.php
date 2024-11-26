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
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Pseudo',
                'required' => true,
                'help' => 'Le pseudo doit contenir entre 3 et 180 caractères.',
                'constraints' => [
                    new NotBlank(['message' => 'Le pseudo est obligatoire']),
                    new Length(['min' => 3, 'max' => 180]),
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
                'constraints' => [
                    new NotBlank(['message' => 'Le numéro de téléphone est obligatoire']),
                    new Length(['min' => 10, 'max' => 15]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est obligatoire']),
                    new Email(['message' => 'Veuillez entrer un email valide']),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false, // Pas lié à l'entité User
                'required' => true,
                'help' => 'Le mot de passe doit contenir au moins 6 caractères.',
                'constraints' => [
                    new NotBlank(['message' => 'Le mot de passe est obligatoire']),
                    new Length(['min' => 6, 'max' => 4096]),
                ],
            ])
            ->add('confirm_password', PasswordType::class, [
                'label' => 'Confirmation du mot de passe',
                'mapped' => false, // Pas lié à l'entité User
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'La confirmation du mot de passe est obligatoire']),
                ],
            ])
            ->add('campus', EntityType::class, [
                'label' => 'Campus',
                'class' => Campus::class,
                'choice_label' => 'name',
                'placeholder' => '-- Choisir un campus --',
                'required' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
            ])
            ->add('photo', FileType::class, [
                'label' => 'Ma photo',
                'required' => false, // PhotoService facultative
                'mapped' => false, // Pas lié directement à l'entité
                'constraints' => [
                    new Image([
                        'maxSize' => '1024k',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Merci de télécharger une image au format JPEG ou PNG.',
                    ]),
                ],
            ]);

        // Ajout conditionnel du champ "deleteImage" si une photo personnalisée existe
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $user = $event->getData();
            if ($user && $user->getPhoto() && $user->getPhoto() !== User::DEFAULT_PHOTO) {
                $event->getForm()->add('deleteImage', CheckboxType::class, [
                    'label' => 'Supprimer ma photo de profil',
                    'mapped' => false,
                    'required' => false,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class, // Lie le formulaire à l'entité User
        ]);
    }
}
