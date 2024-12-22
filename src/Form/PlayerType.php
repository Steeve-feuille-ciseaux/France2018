<?php

namespace App\Form;

use App\Entity\Player;
use App\Entity\Club;
use App\Entity\Pays;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class PlayerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un prénom'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un nom'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer une date de naissance'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('position', ChoiceType::class, [
                'label' => 'Position',
                'choices' => [
                    'Attaquant' => 'Attaquant',
                    'Milieu' => 'Milieu',
                    'Défenseur' => 'Défenseur',
                    'Gardien' => 'Gardien'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner une position'
                    ])
                ],
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('jerseyNumber', IntegerType::class, [
                'label' => 'Numéro',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un numéro'
                    ]),
                    new Range([
                        'min' => 1,
                        'max' => 99,
                        'notInRangeMessage' => 'Le numéro doit être compris entre {{ min }} et {{ max }}'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => 99
                ]
            ])
            ->add('club', EntityType::class, [
                'class' => Club::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisir un club',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner un club'
                    ])
                ],
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('nationality', EntityType::class, [
                'class' => Pays::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisir un pays',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner un pays'
                    ])
                ],
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('worldCups', IntegerType::class, [
                'label' => 'Coupes du monde',
                'required' => false,
                'constraints' => [
                    new Range([
                        'min' => 0,
                        'minMessage' => 'Le nombre de coupes du monde ne peut pas être négatif'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ]
            ])
            ->add('photoFile', FileType::class, [
                'label' => 'Photo',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image JPG ou PNG',
                        'maxSizeMessage' => 'L\'image ne doit pas dépasser 2 Mo'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/jpeg,image/png'
                ]
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
