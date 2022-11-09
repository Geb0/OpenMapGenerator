<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use App\Service\RolesService;
use App\Service\LocalesService;

use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\Form\CallbackTransformer;

/**
 * AdminUserType - User form for administration
 *
 * @property LoggerInterface $logger
 * @property Bool $enableLog
 *
 * @property Array $locales Language locales list for Choice type
 * @property Array $roles User roles list for Choice type
 */
class AdminUserType extends AbstractType
{
    private $logger;
    private $enableLog;

    private $locales;
    private $roles;

    /**
     * Class constructor - Get system parameters and log tool
     *
     * @param LoggerInterface $logger Application log interface
     * @param ParameterBagInterface $params Bag to get system parameters
     * @param LocalesService $locales List of available locales
     * @param RolesService $roles List of available roles
     */
    public function __construct(
        LoggerInterface $logger,
        ParameterBagInterface $params,
        LocalesService $locales,
        RolesService $roles,
    )
    {
        $this->logger = $logger;
        $this->enableLog = $params->get('app.enableLog');

        if($this->enableLog)
        {
            $this->logger->notice("AdminUserType::__construct()");
        }

        $this->locales = $locales->getListForChoiceType();
        $this->roles = $roles->getListForChoiceType();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if($this->enableLog)
        {
            $this->logger->notice("AdminUserType::buildForm()");
        }

        $builder
            ->add('name', Type\TextType::class,
                [
                    'attr' => [
                        'minlength' => '3',
                        'maxlength' => '30',
                    ],
                    'label' => 'Name',
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
                    'label' => 'E-mail',
                    'required' => true,
                    'constraints' => [
                        new Assert\Length(['min' => 5, 'max' => 180]),
                        new Assert\NotBlank(),
                        new Assert\Email(),
                    ],
                ]
            )
            ->add('language', Type\ChoiceType::class,
                [
                    'label' => 'Language',
                    'required' => true,
                    'choices'  => $this->locales,
                ]
            )
            ->add('recoverkey', Type\TextType::class,
                [
                    'label' => 'Recover key',
                    'required' => false,
                ]
            )
            ->add('roles', Type\ChoiceType::class,
                [
                    'label' => 'Role',
                    'required' => true,
                    'multiple' => false,
                    'expanded' => false,
                    'choices'  => $this->roles,
                ]
            )
            ->add('save', Type\SubmitType::class,
                [
                    'attr' => [
                        'class' => 'button-primary',
                    ],
                    'label' => 'button.update',
                ]
            )
            ->add('reset', Type\ResetType::class,
                [
                    'attr' => [
                        'class' => 'button-reset',
                    ],
                    'label' => 'button.reset',
                ]
            )
        ;

        // Data transformer
        // From https://dthlabs.com/select-or-checkbox-in-a-symfony5-form-for-user-roles/

        $builder->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                function ($rolesArray) {
                     // transform the array to a string
                     return count($rolesArray)? $rolesArray[0]: null;
                },
                function ($rolesString) {
                     // transform the string back to an array
                     return [$rolesString];
                }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        if($this->enableLog)
        {
            $this->logger->notice("AdminUserType::configureOptions()");
        }

        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
