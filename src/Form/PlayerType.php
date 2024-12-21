<?php

namespace App\Form;

use App\Entity\Player;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PlayerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['class' => 'form-control']
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control']
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('position', ChoiceType::class, [
                'label' => 'Poste',
                'choices' => [
                    'Gardien' => 'Gardien',
                    'Défenseur' => 'Défenseur',
                    'Milieu' => 'Milieu',
                    'Attaquant' => 'Attaquant'
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('jerseyNumber', IntegerType::class, [
                'label' => 'Numéro de maillot',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => 99
                ]
            ])
            ->add('currentClub', TextType::class, [
                'label' => 'Club actuel',
                'attr' => ['class' => 'form-control']
            ])
            ->add('nationality', TextType::class, [
                'label' => 'Nationalité',
                'attr' => ['class' => 'form-control']
            ])
            ->add('worldCups', IntegerType::class, [
                'label' => 'Coupes du Monde',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ]
            ])
            ->add('championsLeague', IntegerType::class, [
                'label' => 'Ligues des Champions',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ]
            ])
            ->add('europeLeague', IntegerType::class, [
                'label' => 'Ligues Europa',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ]
            ])
            ->add('nationalChampionship', IntegerType::class, [
                'label' => 'Championnats Nationaux',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ]
            ])
            ->add('nationalCup', IntegerType::class, [
                'label' => 'Coupes Nationales',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ]
            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo du joueur',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG ou PNG)',
                    ])
                ],
                'attr' => ['class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Player::class,
        ]);
    }
}
