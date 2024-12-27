<?php

namespace App\Form;

use App\Entity\Profil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdminUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, [
                'attr' => ['class' => 'form-control']
            ])
            ->add('password', PasswordType::class, [
                'attr' => ['class' => 'form-control'],
                'required' => $options['require_password'],
                'mapped' => false,
                'constraints' => $options['require_password'] ? [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                ] : [],
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => 1,
                    'Créateur' => 2,
                    'Modérateur' => 3,
                    'Administrateur' => 4
                ],
                'attr' => ['class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Profil::class,
            'require_password' => false,
        ]);
    }
}
