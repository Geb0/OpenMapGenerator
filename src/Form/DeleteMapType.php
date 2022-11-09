<?php

namespace App\Form;

use App\Entity\MAPmap;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type;

/**
 * DeleteMapType - Map deletion form for user management
 */
class DeleteMapType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void
    {
        $builder
            ->add('name', Type\TextType::class,
                [
                    'attr' => [
                        'readonly' => 'readonly',
                    ],
                    'label' => 'label.mapName',
                    'required' => false,
                    'empty_data' => '',
                ]
            )
            ->add('description', Type\TextareaType::class,
                [
                    'attr' => [
                        'readonly' => 'readonly',
                    ],
                    'label' => 'label.description',
                    'required' => false,
                    'empty_data' => '',
                ]
            )
            ->add('private', Type\CheckboxType::class,
                [
                    'attr' => [
                        'readonly' => 'readonly',
                    ],
                    'label' => 'label.private',
                    'required' => false,
                    'empty_data' => null,
                ]
            )
            ->add('password', Type\TextType::class,
                [
                    'attr' => [
                        'readonly' => 'readonly',
                    ],
                    'label' => 'label.mapPassword',
                    'required' => false,
                    'empty_data' => '',
                ]
            )
            ->add('save', Type\SubmitType::class,
                [
                    'attr' => [
                        'class' => 'button-primary',
                    ],
                    'label' => 'button.deleteMap',
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MAPmap::class,
        ]);
    }
}
