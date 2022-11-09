<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * IconsService - Icons service administration
 *
 * @property Boolean $enableLog Application log enabled or not
 * @property LoggerInterface $logger Application log interface
 * @property TranslatorInterface $translator
 *
 * @property Array $icons Icons list
 * @property String $defaultIcon Default icon
 *
 * @method Array getList() Get icons list with translated name
 * @method Array getListForChoiceType() Get icons list to use in Form ChoiceType
 * @method String getValidIcon() Get icon code if exists, otherwise default icon
 */
class IconsService extends AbstractController
{
    private $enableLog;
    private $logger;
    private $translator;

    private $icons;
    private $defaultIcon;

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
            $this->logger->notice("IconsService::__construct()");
        }

        $this->translator = $translator;

        $dir = str_replace('//', '/', $_SERVER["DOCUMENT_ROOT"].'/'.$params->get('app.iconsDir').'/');

        // Load icons list from disk in directory app.iconsDir

        $files = opendir($dir);

        if($files) {

            $iconFiles = [];

            while(($file = readdir($files)) !== false) {

                // Exclude subdirectories

                if(is_file($dir.$file)) {

                    $iconFiles[] = $file;
                }
            }
        }
        closedir($files);

        $this->icons = $iconFiles;
        $this->defaultIcon = $params->get('app.defaultIcon');
    }

    /**
     * getList - Get icons list with translated name
     *
     * @return Array Icons list
     */
    public function getList(): Array
    {
        if($this->enableLog)
        {
            $this->logger->notice("IconsService::getList()");
        }

        $iconsList = [];

        foreach($this->icons as $icon)
        {
            $iconName = 'icon.'.substr($icon, 0, strrpos($icon, '.'));
            $iconsList[$icon] = $this->translator->trans($iconName);
        }

        asort($iconsList);

        return $iconsList;
    }

    /**
     * getListForChoiceType - Get icons list to use in Form ChoiceType
     *
     * @return Array Icons list ['Translated icon name' => 'icon file',]
     */
    public function getListForChoiceType(): Array
    {
        if($this->enableLog)
        {
            $this->logger->notice("IconsService::getListForChoiceType()");
        }

        // Get icons list with translated names

        $iconsList = [];

        foreach($this->icons as $icon)
        {
            $iconName = 'icon.'.substr($icon, 0, strrpos($icon, '.'));
            $iconsList[$icon] = $this->translator->trans($iconName);
        }

        // Sort array by icon name

        asort($iconsList);

        // Invert key => val for ChoiceType

        $outList = [];

        foreach($iconsList as $key => $val)
        {
            $outList[$val] = $key;
        }

        return $outList;
    }

    /**
     * getValidIcon - Get icon code if exists, otherwise default icon
     *
     * @param String $icon Icon code to verify
     *
     * @return String Valid icon code
     */
    public function getValidIcon(String $icon): String
    {
        if($this->enableLog)
        {
            $this->logger->notice("IconsService::getValidIcon($icon)");
        }

        if(in_array($icon, $this->icons))
        {
            return $icon;

        } else {

            return $this->defaultIcon;
        }
    }
}
