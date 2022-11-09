<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * HelpController - User help
 *
 * @property Boolean $enableLog Application log enabled or not
 * @property LoggerInterface $logger Application log interface
 *
 * @method Response index() Show help page
 * @method Response license() Show license page
 * @method Response termsOfUse() Show Terms of use page
 */
class HelpController extends AbstractController
{
    private $enableLog;
    private $logger;

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
            $this->logger->notice("HelpController::__construct()");
        }
    }

    /**
     * index - Show help page
     *
     * Route: help.index, /help, method: GET
     *
     * @return Response
     */
    #[Route('/{_locale}/help', name: 'help.index', methods: ['GET'], requirements: ["_locale" => "%app.localesString%"])]
    public function index(): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("HelpController::index()");
        }

        return $this->render('help/index.html.twig', [
            'currentMenu' => 'help'
        ]);
    }

    /**
     * license - Show license page
     *
     * Route: help.license, /license, method: GET
     *
     * @return Response
     */
    #[Route('/{_locale}/license', name: 'help.license', methods: ['GET'], requirements: ["_locale" => "%app.localesString%"])]
    public function license(): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("HelpController::license()");
        }

        return $this->render('help/license.html.twig', [
            'currentMenu' => 'help'
        ]);
    }

    /**
     * termsOfUse - Show Terms of use page
     *
     * Route: help.tou, /tou, method: GET
     *
     * @return Response
     */
    #[Route('/{_locale}/tou', name: 'help.tou', methods: ['GET'], requirements: ["_locale" => "%app.localesString%"])]
    public function termsOfUse(): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("HelpController::tou()");
        }

        return $this->render('help/tou.html.twig', [
            'currentMenu' => 'help'
        ]);
    }
}
