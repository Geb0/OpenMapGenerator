<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * RolesService - Roles service administration
 *
 * @property Boolean $enableLog Application log enabled or not
 * @property LoggerInterface $logger Application log interface
 *
 * @property Array $roles Application roles list
 *
 * @method Array getListForChoiceType() Get roles list to use in Form ChoiceType
 */
class RolesService extends AbstractController
{
    private $enableLog;
    private $logger;

    private $roles;

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
            $this->logger->notice("RolesService::__construct()");
        }

        // Get roles from security.role_hierarchy.roles

        $this->roles = [
            'ROLE_USER' => $translator->trans('ROLE_USER')
        ];

        foreach($params->get('security.role_hierarchy.roles') as $code => $roles)
        {
            $this->roles[$code] = $translator->trans($code);
        }
    }

    /**
     * getListForChoiceType - Get roles list to use in Form ChoiceType
     *
     * @return Array Roles list ['Role name' => 'Role code',]
     */
    public function getListForChoiceType(): Array
    {
        if($this->enableLog)
        {
            $this->logger->notice("RolesService::getListForChoiceType()");
        }

        $roles = [];

        foreach($this->roles as $key => $val)
        {
            $roles[$val] = $key;
        }
        return $roles;
    }
}
