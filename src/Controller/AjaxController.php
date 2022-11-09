<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\MAPmap;
use App\Entity\MAPlocation;

use App\Repository\MAPmapRepository;
use App\Repository\MAPlocationRepository;

use App\Form\NewMapType;
use App\Form\DeleteMapType;

use App\Service\IconsService;

/**
 * AjaxController - Ajax management
 *
 * @property String $defaultLocale Default locale
 * @property IconsService $icons Icons management service
 *
 * @method JsonResponse getMessages() Get JS messages in user language
 * @method JsonResponse mapUpdate() Update map parameters (name, description, private, restricted)
 * @method JsonResponse mapCenterUpdate() Update map parameters (latitude, longitude, zoom)
 * @method JsonResponse locationCreate() Create map location
 * @method JsonResponse locationUpdate() Update map location parameters
 * @method JsonResponse locationDelete() Remove map location
 */
class AjaxController extends AbstractController
{
    private $defaultLocale;
    private $icons;

    /**
     * Class constructor - Get system parameters and log tool
     *
     * @param LoggerInterface $logger Application log interface
     * @param ParameterBagInterface $params Bag to get system parameters
     * @param IconsService $icons Icons management service
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
            $this->logger->notice("AjaxController::__construct()");
        }

        $this->icons = $icons;

        $this->defaultLocale = $params->get('app.defaultLocale');
    }

    /**
     * getMessages - Get JS messages in user language
     *
     * Route: ajax.get.messages, /ajax/getMessages, method: GET, POST
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     */
    #[Route('/ajax/getMessages', name: 'ajax.get.messages', methods: ['GET', 'POST'])]
    public function getMessages(
        Request $request,
        TranslatorInterface $translator,
    ): JsonResponse
    {
        $lang = $this->defaultLocale;

        if($request->isXmlHttpRequest())
        {
            $datas = $request->request->all();
            $lang = $datas['lang'];
        }
        // Javascript messages

        $jsMessages = [

            'generatePopupMoreInfos',
            'generatePopupWith',
            'toRelocateLocation',

            'responseUpdateOK',
            'responseUpdateKO',

            'responseUpdateCenterOK',
            'responseUpdateCenterKO',

            'responseCreateLocationOK',
            'responseCreateLocationKO',
            'responseUpdateLocationOK',
            'responseUpdateLocationKO',
            'responseDeleteLocationOK',
            'responseDeleteLocationKO',

            'updateMapEmptyName',
            'createMarkerAlreadyExists',
            'updateMarkerError',
            'updateMarkerEmptyName',
        ];

        $datas = [];

        foreach($jsMessages as $message)
        {
            $datas[$message] = $translator->trans('js.'.$message, [], 'messages', $lang);
        }

        return new JsonResponse($datas);
    }

    /**
     * mapUpdate - Update map parameters (name, description, private, restricted)
     *
     * Route: ajax.map.update, /ajax/mapUpdate/{id}, method: POST
     *
     * Security: is_granted('ROLE_USER') and user === map.getUser()
     *
     * @param MAPmap $map
     * @param Request $request
     * @param EntityManagerInterface $manager
     *
     * @return JsonResponse ['result': true/false]
     */
    #[Route('/ajax/mapUpdate/{id}', name: 'ajax.map.update', methods: ['POST'])]
    #[Security("is_granted('ROLE_USER') and user === map.getUser()", statusCode: 403, message: "ajax.location.update - User tried to update a location that does not belong to him.")]
    public function mapUpdate(
        MAPmap $map,
        Request $request,
        EntityManagerInterface $manager
    ): JsonResponse
    {
        if($this->enableLog)
        {
            $this->logger->notice("AjaxController::mapUpdate()");
        }

        $jsonData = array();
        $result = false;

        if($request->isXmlHttpRequest())
        {
            $datas = $request->request->all();

            $map->setName($datas['name']);
            $map->setDescription($datas['description']);
            $map->setPrivate($datas['private']);
            $map->setPassword($datas['password']);
            $manager->persist($map);
            $manager->flush();
            $result = true;
        }

        $jsonData['result'] = $result;
        return new JsonResponse($jsonData);
    }

    /**
     * mapCenterUpdate - Update map parameters (latitude, longitude, zoom)
     *
     * Route: ajax.map.center.update, /ajax/mapCenterUpdate/{id}, method: POST
     *
     * Security: is_granted('ROLE_USER') and user === map.getUser()
     *
     * @param MAPmap $map
     * @param Request $request
     * @param EntityManagerInterface $manager
     *
     * @return JsonResponse ['result': true/false]
     */
    #[Route('/ajax/mapCenterUpdate/{id}', name: 'ajax.map.center.update', methods: ['POST'])]
    #[Security("is_granted('ROLE_USER') and user === map.getUser()", statusCode: 403, message: "ajax.location.update - User tried to update a location that does not belong to him.")]
    public function mapCenterUpdate(
        MAPmap $map,
        Request $request,
        EntityManagerInterface $manager
    ): JsonResponse
    {
        if($this->enableLog)
        {
            $this->logger->notice("AjaxController::mapCenterUpdate()");
        }

        $jsonData = array();
        $result = false;

        if($request->isXmlHttpRequest())
        {
            $datas = $request->request->all();

            $map->setLatitude($datas['lat']);
            $map->setLongitude($datas['lng']);
            $map->setZoom($datas['zoom']);
            $manager->persist($map);
            $manager->flush();
            $result = true;
        }

        $jsonData['result'] = $result;
        return new JsonResponse($jsonData);
    }

    /**
     * locationCreate - Create map location
     *
     * Route: ajax.location.create, /ajax/locationCreate/{id}, method: POST
     *
     * Security: is_granted('ROLE_USER') and user === map.getUser()
     *
     * @param MAPmap $map
     * @param Request $request
     * @param EntityManagerInterface $manager
     *
     * @return JsonResponse ['result': true/false, 'newId': new location identifier]
     */
    #[Route('/ajax/locationCreate/{id}', name: 'ajax.location.create', methods: ['POST'])]
    #[Security("is_granted('ROLE_USER') and user === map.getUser()", statusCode: 403, message: "ajax.location.create - User tried to create a location in a map that does not belong to him.")]
    public function locationCreate(
        MAPmap $map,
        Request $request,
        EntityManagerInterface $manager
    ): JsonResponse
    {
        if($this->enableLog)
        {
            $this->logger->notice("AjaxController::locationCreate()");
        }

        $jsonData = array();
        $result = false;

        if($request->isXmlHttpRequest())
        {
            $datas = $request->request->all();

            // Verify if location not already exists
            // with same latitude and longitude

            $locationExists = false;

            foreach($map->getLocations() as $location)
            {
                if(
                    $location->getLatitude() == $datas['lat']
                    &&
                    $location->getLongitude() == $datas['lng']
                ) {
                    $locationExists = true;
                    break;
                }
            }

            // Create location if not already exists

            if(!$locationExists)
            {
                $location = new MAPlocation();
                $location->setMap($map);
                $location->setLatitude($datas['lat']);
                $location->setLongitude($datas['lng']);
                $location->setName($datas['name']);
                $location->setDescription($datas['description']);
                $location->setIcon($this->icons->getValidIcon($datas['icon']));
                $location->setLink($datas['link']);
                $manager->persist($location);
                $manager->flush();
                $result = true;
                $jsonData['newId'] = $location->getId();
            }
        }

        $jsonData['result'] = $result;
        return new JsonResponse($jsonData);
    }

    /**
     * locationUpdate - Update map location parameters
     *
     * Route: ajax.location.update, /ajax/locationUpdate/{id}, method: POST
     *
     * Security: is_granted('ROLE_USER') and user === location.getMap().getUser()
     *
     * @param MAPlocation $location
     * @param Request $request
     * @param EntityManagerInterface $manager
     *
     * @return JsonResponse ['result': true/false]
     */
    #[Route('/ajax/locationUpdate/{id}', name: 'ajax.location.update', methods: ['POST'])]
    #[Security("is_granted('ROLE_USER') and user === location.getMap().getUser()", statusCode: 403, message: "ajax.location.update - User tried to update a location that does not belong to him.")]
    public function locationUpdate(
        MAPlocation $location,
        Request $request,
        EntityManagerInterface $manager
    ): JsonResponse
    {
        if($this->enableLog)
        {
            $this->logger->notice("AjaxController::locationUpdate()");
        }

        $jsonData = array();
        $result = false;

        if($request->isXmlHttpRequest())
        {
            $datas = $request->request->all();
            $location->setName($datas['name']);
            $location->setDescription($datas['description']);
            $location->setLatitude($datas['lat']);
            $location->setLongitude($datas['lng']);
            $location->setIcon($this->icons->getValidIcon($datas['icon']));
            $location->setLink($datas['link']);
            $manager->persist($location);
            $manager->flush();
            $result = true;
        }

        $jsonData['result'] = $result;
        return new JsonResponse($jsonData);
    }

    /**
     * locationDelete - Remove map location
     *
     * Route: ajax.location.delete, /ajax/locationDelete/{id}, method: POST
     *
     * Security: is_granted('ROLE_USER') and user === location.getMap().getUser()
     *
     * @param MAPlocation $location
     * @param Request $request
     * @param EntityManagerInterface $manager
     *
     * @return JsonResponse ['result': true/false]
     */
    #[Route('/ajax/locationDelete/{id}', name: 'ajax.location.delete', methods: ['POST'])]
    #[Security("is_granted('ROLE_USER') and user === location.getMap().getUser()", statusCode: 403, message: "ajax.location.delete - User tried to delete a location that does not belong to him.")]
    public function locationDelete(
        MAPlocation $location,
        Request $request,
        EntityManagerInterface $manager
    ): JsonResponse
    {
        if($this->enableLog)
        {
            $this->logger->notice("AjaxController::locationDelete()");
        }

        $jsonData = array();
        $result = false;

        if($request->isXmlHttpRequest())
        {
            $manager->remove($location);
            $manager->flush();
            $result = true;
        }

        $jsonData['result'] = $result;
        return new JsonResponse($jsonData);
    }
}
