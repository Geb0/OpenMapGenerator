<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use App\Entity\MAPmap;
use App\Entity\MAPlocation;

use App\Repository\MAPmapRepository;
use App\Repository\MAPlocationRepository;

use App\Form\NewMapType;
use App\Form\DeleteMapType;

use App\Service\IconsService;

/**
 * MapController - Map show and administration
 *
 * @property Boolean $enableLog Application log enabled or not
 * @property LoggerInterface $logger Application log interface
 *
 * @property Integer $linesPerPage Number of lines for lists pagination
 * @property IconsService $icons Icons management service
 *
 * @method Response mapShow() Show public (and user, if connected) maps list
 * @method Response mapSearchByName() Search public (and user, if connected) maps by name
 * @method Response mapSearch() Show maps search by name result
 * @method Response mapShowMap() Show map
 * @method Response mapShowMapById() Show search by id map result
 * @method Response showMapRestricted() Show restricted map with id and password
 * @method Response mapManage() Show user maps list for update
 * @method Response mapNew() Create new user map
 * @method Response mapUpdate() Update map and locations
 * @method Response mapToDelete() Delete map confirmation request
 * @method Response mapDelete() Delete map
 */
class MapController extends AbstractController
{
    private $enableLog;
    private $logger;

    private $linesPerPage;
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
            $this->logger->notice("MapController::__construct()");
        }

        $this->linesPerPage = $params->get('app.linesPerPage');
        $this->icons = $icons;
    }

    /**
     * mapShow - Show public (and user, if connected) maps list
     *
     * Route: map.show, /show, method: GET
     *
     * @param MAPmapRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     *
     * @return Response
     */
    #[Route('/{_locale}/show', name: 'map.show', methods: ['GET'], requirements: ["_locale" => "%app.localesString%"])]
    public function mapShow(
        MAPmapRepository $repository,
        PaginatorInterface $paginator,
        Request $request
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("MapController::mapShow()");
        }

        $paginatedMaps = $paginator->paginate(
            $repository->findMaps(
                $this->getUser() ? $this->getUser()->getId() : 0
            ),
            $request->query->getInt('page', 1),
            $this->linesPerPage
        );

        return $this->render('map/show.html.twig', [
            'currentMenu' => 'show',
            'maps' => $paginatedMaps,
            'searchName' => ''
        ]);
    }

    /**
     * mapSearchByName - Search public (and user, if connected) maps by name
     *
     * Route: map.tosearch, /tosearch, method: POST
     *
     * @param Request $request
     *
     * @return Response
     */
    #[Route('/{_locale}/tosearch', name: 'map.tosearch', methods: ['POST'], requirements: ["_locale" => "%app.localesString%"])]
    public function mapSearchByName(
        Request $request
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("MapController::mapSearchByName()");
        }

        $name = strval($request->request->get('searchName'));

        if($name === null || $name === '')
        {
            return $this->redirectToRoute('map.show');

        } else {

            return $this->redirectToRoute('map.search', ['name' => $name]);
        }
    }

    /**
     * mapSearch - Show maps search by name result
     *
     * Route: map.search, /search, method: GET
     *
     * @param MAPmapRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param String $name - Maps name to search
     *
     * @return Response
     */
    #[Route('/{_locale}/search/{name}', name: 'map.search', methods: ['GET'], requirements: ["_locale" => "%app.localesString%"])]
    public function mapSearch(
        MAPmapRepository $repository,
        PaginatorInterface $paginator,
        Request $request,
        String $name = ''
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("MapController::mapSearch($name)");
        }

        $maps = $repository->findMapsByName(
            $this->getUser() ? $this->getUser()->getId() : 0,
            $name
        );

        $paginatedMaps = $paginator->paginate(
            $maps,
            $request->query->getInt('page', 1),
            $this->linesPerPage
        );

        return $this->render('map/show.html.twig', [
            'currentMenu' => 'show',
            'maps' => $paginatedMaps,
            'searchName' => $name,
        ]);
    }

    /**
     * mapShowMap - Show map
     *
     * Route: map.show.map, /show/{id}, method: GET
     *
     * @param MAPmap $map
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    #[Route('/{_locale}/show/{id}', name: 'map.show.map', methods: ['GET'], requirements: ["_locale" => "%app.localesString%"])]
    public function mapShowMap(
        MAPmap $map,
        TranslatorInterface $translator,
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("MapController::mapShowMap()");
        }

        if(
            $map->getUser() != $this->getUser()
            && (
                $map->isPrivate()
                ||
                $map->getPassword() !== ''
            )
        )
        {
            $this->addFlash('error', $translator->trans('error.accessingMap'));
            return $this->redirectToRoute('map.show');
        }

        return $this->render('map/show.map.html.twig', [
            'currentMenu' => 'show',
            'map' => $map,
            'locations' => $map->getLocations(),
        ]);
    }

    /**
     * mapShowMapById - Show search by id map result
     *
     * Route: map.show.map.by.id, /showById, method: POST
     *
     * @param MAPmapRepository $repository
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    #[Route('/{_locale}/showById', name: 'map.show.map.by.id', methods: ['POST'], requirements: ["_locale" => "%app.localesString%"])]
    public function mapShowMapById(
        MAPmapRepository $repository,
        Request $request,
        TranslatorInterface $translator
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("MapController::mapShowMapById()");
        }

        $id = intval($request->request->get('searchId'));
        $map = $repository->findOneBy(['id' => $id]);

        if($map)
        {
            return $this->redirectToRoute('map.show.map', ['id' => $id]);

        } else {

            $this->addFlash('error', $translator->trans('error.accessingMap'));
            return $this->redirectToRoute('map.show');
        }
    }

    /**
     * showMapRestricted - Show restricted map with id and password
     *
     * Route: map.show.map.restricted, /showmaprestricted, method: GET, POST
     *
     * @param MAPmapRepository $repository
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    #[Route('/{_locale}/showmaprestricted', name: 'map.show.map.restricted', methods: ['GET', 'POST'], requirements: ["_locale" => "%app.localesString%"])]
    public function mapShowRestricted(
        MAPmapRepository $repository,
        Request $request,
        TranslatorInterface $translator
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("MapController::mapShowRestricted()");
        }

        $params = $request->request->all();

        if($params['_mapid'] !== '' && $params['_mappassword'] !== '')
        {
            $map = $repository->findOneBy(['id' => intval($params['_mapid'])]);

            if(
                $map
                && (
                    $map->getPassword() === $params['_mappassword']
                    && !$map->isPrivate()
                    ||
                    $map->getPassword() === $params['_mappassword']
                    && $map->getUser() === $this->getUser()
                )
            )
            {
                return $this->render('map/show.map.html.twig', [
                    'currentMenu' => 'show',
                    'map' => $map,
                    'locations' => $map->getLocations(),
                ]);
            }
        }

        $this->addFlash('error', $translator->trans('error.accessingMap'));
        return $this->redirectToRoute('map.show');
    }

    /**
     * mapManage - Show user maps list for update
     *
     * Route: map.manage, /manage, method: GET
     *
     * Security: is_granted('ROLE_USER')
     *
     * @param MAPmapRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     *
     * @return Response
     */
    #[Route('/{_locale}/manage', name: 'map.manage', methods: ['GET'], requirements: ["_locale" => "%app.localesString%"])]
    #[IsGranted('ROLE_USER')]
    public function mapManage(
        MAPmapRepository $repository,
        PaginatorInterface $paginator,
        Request $request
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("MapController::mapManage()");
        }

        $paginatedMaps = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1),
            $this->linesPerPage
        );

        return $this->render('map/manage.html.twig', [
            'currentMenu' => 'manage',
            'maps' => $paginatedMaps,
        ]);
    }

    /**
     * mapNew - Create new user map
     *
     * Route: map.manage.new, /manage/new, method: GET, POST
     *
     * Security: is_granted('ROLE_USER')
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    #[Route('/{_locale}/manage/new', name: 'map.manage.new', methods: ['GET', 'POST'], requirements: ["_locale" => "%app.localesString%"])]
    #[IsGranted('ROLE_USER')]
    public function mapNew(
        Request $request,
        EntityManagerInterface $manager,
        TranslatorInterface $translator
    ) : Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("MapController::mapNew()");
        }

        $map = new MAPmap();
        $form = $this->createForm(NewMapType::class, $map);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $map = $form->getData();
            $map->setUser($this->getUser());
            $manager->persist($map);
            $manager->flush();
            $this->addFlash('info', $translator->trans('info.mapCreated', ['{id}' => $map->getId()]));
            return $this->redirectToRoute('map.manage.update', ['id' => $map->getId()]);
        }

        return $this->render('map/manage.new.html.twig', [
            'currentMenu' => 'manage',
            'form' => $form->createView(),
        ]);
    }

    /**
     * mapUpdate - Update map and locations
     *
     * Route: map.manage.update, /manage/update/{id}, method: GET, POST
     *
     * Security: is_granted('ROLE_USER') and user === map.getUser()
     *
     * @param MAPmap $map
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    #[Route('/{_locale}/manage/update/{id}', name: 'map.manage.update', methods: ['GET', 'POST'], requirements: ["_locale" => "%app.localesString%"])]
    #[Security("is_granted('ROLE_USER') and user === map.getUser()", statusCode: 403, message: "map.manage.update - User tried to update a map that does not belong to him.")]
    public function mapUpdate(
        MAPmap $map,
        Request $request,
        TranslatorInterface $translator
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("MapController::mapUpdate()");
        }

        return $this->render('map/manage.update.html.twig', [
            'currentMenu' => 'manage',
            'map' => $map,
            'locations' => $map->getLocations(),
            'icons' => $this->icons->getList(),
        ]);
    }

    /**
     * mapToDelete - Delete map confirmation request
     *
     * Route: map.manage.todelete, /manage/todelete/{id}, method: GET, POST
     *
     * Security: is_granted('ROLE_USER') and user === map.getUser()
     *
     * @param MAPmap $map
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    #[Route('/{_locale}/manage/todelete/{id}', name: 'map.manage.todelete', methods: ['GET', 'POST'], requirements: ["_locale" => "%app.localesString%"])]
    #[Security("is_granted('ROLE_USER') and user === map.getUser()", statusCode: 403, message: "map.manage.todelete - User tried to delete a map that does not belong to him.")]
    public function mapToDelete(
        MAPmap $map,
        Request $request,
        EntityManagerInterface $manager
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("MapController::mapToDelete()");
        }

        $form = $this->createForm(DeleteMapType::class, $map);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $map = $form->getData();
            return $this->redirectToRoute('map.manage.delete', ['id' => $map->getId()]);
        }

        return $this->render('map/manage.todelete.html.twig', [
            'currentMenu' => 'manage',
            'form' => $form->createView(),
            'mapId' => $map->getId(),
        ]);
    }

    /**
     * mapDelete - Delete map
     *
     * Route: map.manage.delete, /manage/delete/{id}, method: GET
     *
     * Security: is_granted('ROLE_USER') and user === map.getUser()
     *
     * @param MAPmap $map
     * @param EntityManagerInterface $manager
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    #[Route('/{_locale}/manage/delete/{id}', name: 'map.manage.delete', methods: ['GET'], requirements: ["_locale" => "%app.localesString%"])]
    #[Security("is_granted('ROLE_USER') and user === map.getUser()", statusCode: 403, message: "map.manage.delete - User tried to delete a map that does not belong to him.")]
    public function mapDelete(
        MAPmap $map,
        EntityManagerInterface $manager,
        TranslatorInterface $translator
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("MapController::mapDelete()");
        }

        $manager->remove($map);
        $manager->flush();
        $this->addFlash('info', $translator->trans('info.mapDeleted'));
        return $this->redirectToRoute('map.manage');
    }
}
