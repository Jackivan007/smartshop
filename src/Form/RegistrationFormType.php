<?php

namespace App\Form;

use App\Entity\Usuario;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre',
            ])
            ->add('apellidos', TextType::class, [
                'label' => 'Apellidos',
            ])
            ->add('username', TextType::class, [
                'label' => 'Nombre de usuario',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Correo electrónico',
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => [
                    'label' => 'Contraseña',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'required' => true,
                        'minlength' => 6,
                        'pattern' => '.{6,}',
                        'title' => 'La contraseña debe tener al menos 6 caracteres',
                    ],
                ],
                'second_options' => [
                    'label' => 'Repite la contraseña',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'required' => true,
                        'minlength' => 6,
                        'pattern' => '.{6,}',
                        'title' => 'Debe coincidir con la contraseña anterior y tener al menos 6 caracteres',
                    ],
                ],
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor, introduce una contraseña',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Tu contraseña debe tener al menos {{ limit }} caracteres',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'Aceptar los términos y condiciones',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Debes aceptar los términos y condiciones.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuario::class,
        ]);
    }
}
