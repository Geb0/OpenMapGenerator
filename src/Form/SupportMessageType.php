<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use App\Service\SupportTypesService;
use Gregwar\CaptchaBundle\Type\CaptchaType;

use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SupportMessageType - User creation form for user registration
 *
 * @property Array $types Support message types list for Choice type
 */
class SupportMessageType extends AbstractType
{
    private $types;

    /**
     * Class constructor - Get system parameters and log tool
     *
     * @param LoggerInterface $logger Application log interface
     * @param ParameterBagInterface $params Bag to get system parameters
     * @param SupportTypesService $types List of available support message types
     */
    public function __construct(
        LoggerInterface $logger,
        ParameterBagInterface $params,
        SupportTypesService $types,
    )
    {
        $this->logger = $logger;
        $this->enableLog = $params->get('app.enableLog');

        if($this->enableLog)
        {
            $this->logger->notice("SupportMessageType::__construct()");
        }

        $this->types = $types->getListForChoiceType();
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
            ->add('subject', Type\ChoiceType::class,
                [
                    'label' => 'label.subject',
                    'required' => true,
                    'multiple' => false,
                    'expanded' => false,
                    'choices'  => $this->types,
                ]
            )
            ->add('message', Type\TextareaType::class,
                [
                    'label' => 'label.message',
                    'required' => true,
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                ]
            )
            ->add('captcha', CaptchaType::class)
            ->add('send', Type\SubmitType::class,
                [
                    'attr' => [
                        'class' => 'button-primary',
                    ],
                    'label' => 'button.send',
                ]
            )
        ;
    }
}
