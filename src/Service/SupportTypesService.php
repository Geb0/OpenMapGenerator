<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * SupportTypesService - Support message types service administration
 *
 * @property Boolean $enableLog Application log enabled or not
 * @property LoggerInterface $logger Application log interface
 * @property TranslatorInterface $translator
 *
 * @property Array $types Types for support messages
 *
 * @method Array getListForChoiceType() Get types list to use in Form ChoiceType
 * @method String getLabel() Get support type translated label
 */
class SupportTypesService extends AbstractController
{
    private $enableLog;
    private $logger;
    private $translator;

    private $types;

    /**
     * Class constructor - Get system parameters and log tool
     *
     * @param LoggerInterface $logger Application log interface
     * @param ParameterBagInterface $params Bag to get system parameters
     * @param TranslatorInterface $translator Translation interface
     */
    public function __construct(
        LoggerInterface $logger,
        ParameterBagInterface $params,
        TranslatorInterface $translator,
    ) {

        $this->logger = $logger;
        $this->enableLog = $params->get('app.enableLog');

        if($this->enableLog)
        {
            $this->logger->notice("SupportTypesService::__construct()");
        }

        $this->translator = $translator;

        // Availables types, translations defined with support.{type}

        $this->types = [
            'info',
            'suggest',
            'error',
            'tou',
        ];
    }

    /**
     * getListForChoiceType - Get types list to use in Form ChoiceType
     *
     * @return Array Types list ['Type name' => 'Type code',]
     */
    public function getListForChoiceType(): Array
    {
        if($this->enableLog)
        {
            $this->logger->notice("SupportTypesService::getListForChoiceType()");
        }

        $types = [];

        foreach($this->types as $key)
        {
            $types[$this->translator->trans('support.'.$key)] = $key;
        }
        return $types;
    }

    /**
     * getLabel - Get support type translated label
     *
     * @param String $ref Type reference
     *
     * @return String Translated label name
     */
    public function getLabel(String $ref): String
    {
        if($this->enableLog)
        {
            $this->logger->notice("SupportTypesService::getLabel()");
        }

        if(in_array($ref, $this->types))
        {
            return $this->translator->trans('support.'.$ref);

        } else {

            return $ref;
        }
    }
}
