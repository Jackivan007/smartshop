<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class CambioPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Contraseña actual',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Introduce tu contraseña actual.'
                    ])
                ],
                'attr' => [
                    'required' => true
                ]
            ])
            ->add('newPassword', PasswordType::class, [
                'label' => 'Nueva contraseña',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Introduce una nueva contraseña.'
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'La nueva contraseña debe tener al menos {{ limit }} caracteres.',
                        'max' => 4096,
                    ])
                ],
                'attr' => [
                    'required' => true,
                    'minlength' => 6,
                    'pattern' => '.{6,}',
                    'title' => 'La contraseña debe tener al menos 6 caracteres',
                    'maxlength' => 4096,
                ]
            ]);
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
