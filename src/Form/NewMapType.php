<?php

namespace App\Form;

use App\Entity\MAPmap;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * NewMapType - Map creation form for user management
 */
class NewMapType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class,
                [
                    'attr' => [
                        'minlength' => '3',
                        'maxlength' => '100',
                    ],
                    'label' => 'label.mapName',
                    'required' => true,
                    'constraints' => [
                        new Assert\Length(['min' => 3, 'max' => 100]),
                        new Assert\NotBlank(),
                    ],
                ]
            )
            ->add('description', Type\TextareaType::class,
                [
                    'attr' => [
                        'maxlength' => '255',
                    ],
                    'label' => 'label.description',
                    'required' => false,
                    'empty_data' => '',
                    'constraints' => [
                        new Assert\Length(['max' => 255]),
                    ],
                ]
            )
            ->add('private', Type\CheckboxType::class,
                [
                    'label' => 'label.private',
                    'required' => false,
                    'empty_data' => null,
                ]
            )
            ->add('password', Type\TextType::class,
                [
                    'attr' => [
                        'maxlength' => '30',
                    ],
                    'label' => 'label.mapPassword',
                    'required' => false,
                    'empty_data' => '',
                    'constraints' => [
                        new Assert\Length(['max' => 30]),
                    ],
                ]
            )
            ->add('save', Type\SubmitType::class,
                [
                    'attr' => [
                        'class' => 'button-primary',
                    ],
                    'label' => 'button.createMap',
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
