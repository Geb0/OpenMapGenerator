<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\User;
use App\Entity\MAPmap;
use App\Entity\MAPlocation;

use App\Repository\UserRepository;
use App\Repository\MAPmapRepository;
use App\Repository\MAPlocationRepository;

use App\Form\AdminUserType;
use App\Form\AdminMapType;
use App\Form\AdminLocationType;

/**
 * AdminController - Database administration
 *
 * Security: is_granted('ROLE_ADMIN')
 *
 * @property Boolean $enableLog Application log enabled or not
 * @property LoggerInterface $logger Application log interface
 *
 * @property Integer $linesPerPage Number of lines for lists pagination
 *
 * @method Response index() Administration menu
 * @method Response showUsers() Administration users list
 * @method Response editUser() Administration user edit
 * @method Response showMaps() Show maps list
 * @method Response editMap() Administration map edit
 * @method Response showLocations() Show locations list
 * @method Response editLocation() Administration location edit
 */
#[Security("is_granted('ROLE_ADMIN')")]
class AdminController extends AbstractController
{
    private $enableLog;
    private $logger;

    private $linesPerPage;

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
            $this->logger->notice("AdminController::__construct()");
        }

        $this->linesPerPage = $params->get('app.linesPerPage4admin');
    }

    /**
     * index - Administration menu
     *
     * Route: admin.index, /admin, method: GET
     *
     * @return Response
     */
    #[Route('/{_locale}/admin', name: 'admin.index', methods: ['GET'], requirements: ["_locale" => "%app.localesString%"])]
    public function index(): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("AdminController::index()");
        }

        return $this->render('admin/index.html.twig', [
            'currentMenu' => 'admin',
        ]);
    }

    /**
     * showUsers - Administration users list
     *
     * Route: admin.users, /admin/users, method: GET
     *
     * @param UserRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     *
     * @return Response
     */
    #[Route('/{_locale}/admin/users', name: 'admin.users', methods: ['GET'], requirements: ["_locale" => "%app.localesString%"])]
    public function showUsers(
        UserRepository $repository,
        PaginatorInterface $paginator,
        Request $request
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("AdminController::showUsers()");
        }

        $paginatedList = $paginator->paginate(
            $repository->findAll(),
            $request->query->getInt('page', 1),
            $this->linesPerPage
        );

        return $this->render('admin/users.html.twig', [
            'currentMenu' => 'admin',
            'users' => $paginatedList,
        ]);
    }

    /**
     * editUser - Administration user edit
     *
     * Route: admin.user, /admin/users/{id}, method: GET, POST
     *
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $manager
     *
     * @return Response
     */
    #[Route('/{_locale}/admin/users/{id}', name: 'admin.user', methods: ['GET', 'POST'], requirements: ["_locale" => "%app.localesString%"])]
    public function editUser(
        User $user,
        Request $request,
        EntityManagerInterface $manager,
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("AdminController::editUser()");
        }

        $form = $this->createForm(AdminUserType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('info', "The account informations have been updated.");
            return $this->redirectToRoute('admin.users');
        }

        return $this->render('admin/user.html.twig', [
            'currentMenu' => 'admin',
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * showMaps - Show maps list
     *
     * Route: admin.maps, /admin/maps, method: GET
     *
     * @param MAPmapRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     *
     * @return Response
     */
    #[Route('/{_locale}/admin/maps', name: 'admin.maps', methods: ['GET'], requirements: ["_locale" => "%app.localesString%"])]
    public function showMaps(
        MAPmapRepository $repository,
        PaginatorInterface $paginator,
        Request $request
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("AdminController::showMaps()");
        }

        $paginatedList = $paginator->paginate(
            $repository->findAll(),
            $request->query->getInt('page', 1),
            $this->linesPerPage
        );

        return $this->render('admin/maps.html.twig', [
            'currentMenu' => 'admin',
            'maps' => $paginatedList,
        ]);
    }

    /**
     * editMap - Administration map edit
     *
     * Route: admin.map, /admin/maps/{id}, method: GET, POST
     *
     * @param MAPmap $map
     * @param Request $request
     * @param EntityManagerInterface $manager
     *
     * @return Response
     */
    #[Route('/{_locale}/admin/maps/{id}', name: 'admin.map', methods: ['GET', 'POST'], requirements: ["_locale" => "%app.localesString%"])]
    public function editMap(
        MAPmap $map,
        Request $request,
        EntityManagerInterface $manager,
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("AdminController::editMap()");
        }

        $form = $this->createForm(AdminMapType::class, $map);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($map);
            $manager->flush();
            $this->addFlash('info', "The map informations have been updated.");
            return $this->redirectToRoute('admin.maps');
        }

        return $this->render('admin/map.html.twig', [
            'currentMenu' => 'admin',
            'map' => $map,
            'form' => $form->createView(),
        ]);
    }

    /**
     * showLocations - Show locations list
     *
     * Route: admin.locations, /admin/locations, method: GET
     *
     * @param MAPlocationRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     *
     * @return Response
     */
    #[Route('/{_locale}/admin/locations', name: 'admin.locations', methods: ['GET'], requirements: ["_locale" => "%app.localesString%"])]
    public function showLocations(
        MAPlocationRepository $repository,
        PaginatorInterface $paginator,
        Request $request
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("AdminController::showLocations()");
        }

        $paginatedList = $paginator->paginate(
            $repository->findAll(),
            $request->query->getInt('page', 1),
            $this->linesPerPage
        );

        return $this->render('admin/locations.html.twig', [
            'currentMenu' => 'admin',
            'locations' => $paginatedList,
        ]);
    }

    /**
     * editLocation - Administration location edit
     *
     * Route: admin.location, /admin/locations/{id}, method: GET, POST
     *
     * @param MAPlocation $location
     * @param Request $request
     * @param EntityManagerInterface $manager
     *
     * @return Response
     */
    #[Route('/{_locale}/admin/locations/{id}', name: 'admin.location', methods: ['GET', 'POST'], requirements: ["_locale" => "%app.localesString%"])]
    public function editLocation(
        MAPlocation $location,
        Request $request,
        EntityManagerInterface $manager,
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("AdminController::editLocation()");
        }

        $form = $this->createForm(AdminLocationType::class, $location);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($location);
            $manager->flush();
            $this->addFlash('info', "The location informations have been updated.");
            return $this->redirectToRoute('admin.locations');
        }

        return $this->render('admin/location.html.twig', [
            'currentMenu' => 'admin',
            'location' => $location,
            'form' => $form->createView(),
        ]);
    }
}
