<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * LocalesService - Locales service administration
 *
 * @property Boolean $enableLog Application log enabled or not
 * @property LoggerInterface $logger Application log interface
 *
 * @property Array $locales Locales list
 *
 * @method Array getListForChoiceType() Get locales list to use in Form ChoiceType
 */
class LocalesService extends AbstractController
{
    private $enableLog;
    private $logger;

    private $locales;

    /**
     * Class constructor - Get system parameters and log tool
     *
     * @param LoggerInterface $logger Application log interface
     * @param ParameterBagInterface $params Bag to get system parameters
     */
    public function __construct(
        LoggerInterface $logger,
        ParameterBagInterface $params,
    ) {

        $this->logger = $logger;
        $this->enableLog = $params->get('app.enableLog');

        if($this->enableLog)
        {
            $this->logger->notice("LocalesService::__construct()");
        }

        $this->locales = $params->get('app.locales');
    }

    /**
     * getListForChoiceType - Get locales list to use in Form ChoiceType
     *
     * @return Array locales list ['Locale name' => 'Locale code',]
     */
    public function getListForChoiceType(): Array
    {
        if($this->enableLog)
        {
            $this->logger->notice("LocalesService::getListForChoiceType()");
        }

        $locales = [];

        foreach($this->locales as $key => $val)
        {
            $locales[$val] = $key;
        }
        return $locales;
    }
}
