<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;
use Gregwar\CaptchaBundle\Type\CaptchaType;

/**
 * RegistrationType - User creation form for user regisrtation
 */
class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class,
                [
                    'attr' => [
                        'minlength' => '3',
                        'maxlength' => '30',
                    ],
                    'label' => 'label.name',
                    'required' => true,
                    'constraints' => [
                        new Assert\Length(['min' => 3, 'max' => 30]),
                        new Assert\NotBlank(),
                    ],
                ]
            )
            ->add('email', Type\EmailType::class,
                [
                    'attr' => [
                        'minlength' => '5',
                        'maxlength' => '180',
                    ],
                    'label' => 'label.email',
                    'required' => true,
                    'constraints' => [
                        new Assert\Length(['min' => 5, 'max' => 180]),
                        new Assert\NotBlank(),
                        new Assert\Email(),
                    ],
                ]
            )
            ->add('plainPassword', Type\RepeatedType::class,
                [
                    'type' => Type\PasswordType::class,
                    'first_options' => [
                        'attr' => [
                            'minlength' => '8',
                            'maxlength' => '255',
                        ],
                        'label' => 'label.password',
                        'constraints' => [
                            new Assert\Length(['min' => 8, 'max' => 255]),
                        ],
                    ],
                    'second_options' => [
                        'attr' => [
                            'minlength' => '8',
                            'maxlength' => '255',
                        ],
                        'label' => 'label.confirmPassword',
                        'constraints' => [
                            new Assert\Length(['min' => 8, 'max' => 255]),
                        ],
                    ],
                    'invalid_message' => 'error.passwordConfirm',
                ]
            )
            ->add('captcha', CaptchaType::class)
            ->add('save', Type\SubmitType::class,
                [
                    'attr' => [
                        'class' => 'button-primary',
                    ],
                    'label' => 'button.createAccount',
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
