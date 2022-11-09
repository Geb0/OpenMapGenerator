<?php

namespace App\Form;

use App\Entity\MAPmap;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AdminMapType - Map form for administration
 *
 * @property LoggerInterface $logger
 * @property Bool $enableLog
 */
class AdminMapType extends AbstractType
{
    private $logger;
    private $enableLog;

    /**
     * Class constructor - Get system parameters and log tool
     *
     * @param LoggerInterface $logger Application log interface
     * @param ParameterBagInterface $params Bag to get system parameters
     */
    public function __construct(
        LoggerInterface $logger,
        ParameterBagInterface $params
    )
    {
        $this->logger = $logger;
        $this->enableLog = $params->get('app.enableLog');

        if($this->enableLog)
        {
            $this->logger->notice("AdminMapType::__construct()");
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if($this->enableLog)
        {
            $this->logger->notice("AdminMapType::buildForm()");
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
            ->add('private', Type\CheckboxType::class,
                [
                    'label' => 'Private',
                    'required' => false,
                    'empty_data' => null,
                ]
            )
            ->add('password', Type\TextType::class,
                [
                    'attr' => [
                        'maxlength' => '30',
                    ],
                    'label' => 'Password',
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
            $this->logger->notice("AdminMapType::configureOptions()");
        }

        $resolver->setDefaults([
            'data_class' => MAPmap::class,
        ]);
    }
}
