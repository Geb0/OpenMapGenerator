<?php

namespace App\Form;

use App\Entity\MAPlocation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;

use App\Service\IconsService;

/**
 * AdminLocationType - Location form for administration
 *
 * @property LoggerInterface $logger
 * @property Bool $enableLog
 *
 * @property Array $iconsChoiceList Icons list for Choice type
 */
class AdminLocationType extends AbstractType
{
    private $logger;
    private $enableLog;

    private $iconsChoiceList;

    /**
     * Class constructor - Get system parameters, log tool and set icons list
     *
     * @param LoggerInterface $logger Application log interface
     * @param ParameterBagInterface $params Bag to get system parameters
     * @param IconsService $icons
     */
    public function __construct(
        LoggerInterface $logger,
        ParameterBagInterface $params,
        IconsService $icons,
    )
    {
        $this->logger = $logger;
        $this->enableLog = $params->get('app.enableLog');

        if($this->enableLog)
        {
            $this->logger->notice("AdminLocationType::__construct()");
        }

        $this->iconsChoiceList = $icons->getListForChoiceType();
    }

    public function buildForm(
        FormBuilderInterface $builder,
        Array $options
    ): void
    {
        if($this->enableLog)
        {
            $this->logger->notice("AdminLocationType::buildForm()");
        }

        $builder
            ->add('name', Type\TextType::class,
                [
                    'attr' => [
                        'minlength' => '3',
                        'maxlength' => '100',
                    ],
                    'label' => 'Name',
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
                    'label' => 'Description',
                    'required' => false,
                    'empty_data' => '',
                    'constraints' => [
                        new Assert\Length(['max' => 255]),
                    ],
                ]
            )
            ->add('latitude', Type\NumberType::class,
                [
                    'label' => 'Latitude',
                    'scale' => 5,
                    'required' => true,
                    'empty_data' => '',
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                ]
            )
            ->add('longitude', Type\NumberType::class,
                [
                    'label' => 'Longitude',
                    'scale' => 5,
                    'required' => true,
                    'empty_data' => '',
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                ]
            )
            ->add('icon', Type\ChoiceType::class,
                [
                    'label' => 'Icon',
                    'required' => true,
                    'choices'  => $this->iconsChoiceList,
                ]
            )
            ->add('link', Type\TextType::class,
                [
                    'label' => 'Link',
                    'required' => false,
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
        if($this->enableLog)
        {
            $this->logger->notice("AdminLocationType::configureOptions()");
        }

        $resolver->setDefaults([
            'data_class' => MAPlocation::class,
        ]);
    }
}
