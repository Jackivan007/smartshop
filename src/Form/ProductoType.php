<?php

namespace App\Form;

use App\Entity\Producto;
use App\Entity\Categoria;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Positive;

class ProductoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre',
                'attr' => [
                    'maxlength' => 30,
                ],
                'constraints' => [
                    new Length([
                        'max' => 30,
                        'maxMessage' => 'El nombre no puede tener más de {{ limit }} caracteres.'
                    ])
                ]
            ])
            ->add('cantidad', IntegerType::class, [
                'label' => 'Cantidad',
                'required' => true,
                'attr' => [
                    'min' => 1,
                ],
                'constraints' => [
                    new Positive(['message' => 'La cantidad debe ser mayor que 0.'])
                ]
            ])
            ->add('categoria', EntityType::class, [
                'class' => Categoria::class,
                'choice_label' => 'nombre',
                'label' => 'Categoría',
                'placeholder' => 'Selecciona una categoría',
            ])
            ->add('nota', TextareaType::class, [
                'label' => 'Nota',
                'required' => false,
                'attr' => [
                    'maxlength' => 200,
                    'rows' => 3
                ],
                'constraints' => [
                    new Length([
                        'max' => 200,
                        'maxMessage' => 'La nota no puede tener más de {{ limit }} caracteres.'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Producto::class,
        ]);
    }
}
