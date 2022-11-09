<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UserPasswordType - Change password form for user management
 */
class UserPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('actualPassword', Type\PasswordType::class,
                [
                    'attr' => [
                        'minlength' => '1',
                        'maxlength' => '255',
                    ],
                    'label' => 'label.actualPassword',
                    'required' => true,
                    'constraints' => [
                        new Assert\Length(['min' => 1, 'max' => 255]),
                    ],
                ]
            )
            ->add('newPassword', Type\RepeatedType::class,
                [
                    'type' => Type\PasswordType::class,
                    'first_options' => [
                        'attr' => [
                            'minlength' => '8',
                            'maxlength' => '255',
                        ],
                        'label' => 'label.newPassword',
                        'required' => false,
                        'empty_data' => '',
                        'constraints' => [
                            new Assert\Length(['min' => 8, 'max' => 255]),
                        ],
                    ],
                    'second_options' => [
                        'attr' => [
                            'minlength' => '8',
                            'maxlength' => '255',
                        ],
                        'label' => 'label.newPasswordConfirm',
                        'required' => false,
                        'empty_data' => '',
                        'constraints' => [
                            new Assert\Length(['min' => 8, 'max' => 255]),
                        ],
                    ],
                    'invalid_message' => 'error.confirmNewPassword',
                ]
            )
            ->add('save', Type\SubmitType::class,
                [
                    'attr' => [
                        'class' => 'button-primary',
                    ],
                    'label' => 'button.updatePassword',
                ]
            )
        ;
    }
}
