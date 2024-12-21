<?php

namespace App\Form;

use App\Entity\Card;
use App\Entity\Player;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('player', EntityType::class, [
                'class' => Player::class,
                'choice_label' => function(Player $player) {
                    return $player->getFirstName() . ' ' . $player->getLastName();
                },
                'placeholder' => 'Choisir un joueur...',
                'required' => true,
                'label' => 'Joueur',
                'attr' => [
                    'class' => 'form-select mb-3'
                ]
            ])
            ->add('club', TextType::class, [
                'label' => 'Club',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Nom du club'
                ]
            ])
            ->add('summary', TextareaType::class, [
                'label' => 'Résumé',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Résumé de la carte...'
                ]
            ])
            ->add('notableAction', TextareaType::class, [
                'label' => 'Action marquante',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Action marquante de cette période...'
                ]
            ])
            ->add('number', IntegerType::class, [
                'label' => 'Numéro',
                'attr' => [
                    'min' => 1,
                    'max' => 99,
                    'placeholder' => 'Numéro du joueur'
                ]
            ])
            ->add('position', ChoiceType::class, [
                'label' => 'Position',
                'choices' => [
                    'Attaquant' => 'Attaquant',
                    'Milieu' => 'Milieu',
                    'Défenseur' => 'Défenseur',
                    'Gardien' => 'Gardien'
                ]
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
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
                ]
            ])
            ->add('startSeason', IntegerType::class, [
                'label' => 'Début de saison',
                'attr' => [
                    'min' => 1900,
                    'max' => date('Y'),
                    'placeholder' => 'Année de début'
                ]
            ])
            ->add('endSeason', IntegerType::class, [
                'label' => 'Fin de saison',
                'required' => false,
                'attr' => [
                    'min' => 1900,
                    'max' => date('Y'),
                    'placeholder' => 'Année de fin (optionnel)'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Card::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'card_form'
        ]);
    }
}
