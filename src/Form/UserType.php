<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use App\Service\LocalesService;

/**
 * UserType - User update form for user management
 *
 * @property Array $locales Language locales list for Choice type
 */
class UserType extends AbstractType
{
    private $locales;

    /**
     * Class constructor - Get system parameters and log tool
     *
     * @param LoggerInterface $logger Application log interface
     * @param ParameterBagInterface $params Bag to get system parameters
     * @param LocalesService $locales List of available locales
     */
    public function __construct(
        LoggerInterface $logger,
        ParameterBagInterface $params,
        LocalesService $locales,
    )
    {
        $this->logger = $logger;
        $this->enableLog = $params->get('app.enableLog');

        if($this->enableLog)
        {
            $this->logger->notice("UserType::__construct()");
        }

        $this->locales = $locales->getListForChoiceType();
    }

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
            ->add('language', Type\ChoiceType::class,
                [
                    'label' => 'Language',
                    'required' => true,
                    'choices'  => $this->locales,
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
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
