<?php

namespace App\Form;

use App\Entity\Usuario;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class PerfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre',
                'constraints' => [
                    new NotBlank(['message' => 'El nombre no puede estar vacío.']),
                    new Length([
                        'max' => 30,
                        'maxMessage' => 'El nombre no puede tener más de {{ limit }} caracteres.'
                    ])
                ],
                'attr' => [
                    'maxlength' => 30
                ]
            ])
            ->add('apellidos', TextType::class, [
                'label' => 'Apellidos',
                'constraints' => [
                    new NotBlank(['message' => 'Los apellidos no pueden estar vacíos.']),
                    new Length([
                        'max' => 40,
                        'maxMessage' => 'Los apellidos no pueden tener más de {{ limit }} caracteres.'
                    ])
                ],
                'attr' => [
                    'maxlength' => 40
                ]
            ])
            ->add('username', TextType::class, [
                'label' => 'Nombre de usuario',
                'constraints' => [
                    new NotBlank(['message' => 'El nombre de usuario no puede estar vacío.']),
                    new Length([
                        'max' => 20,
                        'maxMessage' => 'El nombre de usuario no puede tener más de {{ limit }} caracteres.'
                    ])
                ],
                'attr' => [
                    'maxlength' => 20
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Correo electrónico',
                'disabled' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuario::class,
        ]);
    }
}
