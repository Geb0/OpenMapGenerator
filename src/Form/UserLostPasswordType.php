<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;
use Gregwar\CaptchaBundle\Type\CaptchaType;

/**
 * UserLostPasswordType - Recover password request form for user management
 */
class UserLostPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
            ->add('captcha', CaptchaType::class)
            ->add('save', Type\SubmitType::class,
                [
                    'attr' => [
                        'class' => 'button-primary',
                    ],
                    'label' => 'button.getNewPassword',
                ]
            )
        ;
    }
}
