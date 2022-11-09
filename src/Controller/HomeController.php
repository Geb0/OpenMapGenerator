<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Contracts\Translation\TranslatorInterface;

use App\Entity\User;
use App\Form\SupportMessageType;
use App\Service\MailerService;

/**
 * HomeController - Application home
 *
 * @property Boolean $enableLog Application log enabled or not
 * @property LoggerInterface $logger Application log interface
 *
 * @property String  $defaultLocale Application default locale
 * @property Array $availableLocales Application available locales
 *
 * @method Response indexWithoutLocale() Show main page without locale, redirect to main page with locale depending of user locale configuration.
 * @method Response index() Show main page with locale
 * @method Response support() Show contact support page
 */
class HomeController extends AbstractController
{
    private $enableLog;
    private $logger;

    private $defaultLocale;
    private $availableLocales;

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
            $this->logger->notice("HomeController::__construct()");
        }

        $this->defaultLocale = $params->get('app.defaultLocale');
        $this->availableLocales = $params->get('app.locales');
    }

    /**
     * indexWithoutLocale - Application main page without locale, redirect to main page with locale depending of user locale configuration.
     *
     * Route: home.index.without.locale, /, method: GET
     *
     * @param Request $request
     *
     * @return Response
     */
    #[Route('/', name: 'home.index.without.locale', methods: ['GET'])]
    public function indexWithoutLocale(
        Request $request
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("HomeController::indexWithoutLocale()");
        }

        $locale = $this->defaultLocale;

        // Get navigator language if managed by application
        if(array_key_exists($request->getPreferredLanguage(), $this->availableLocales))
        {
            $locale = $request->getPreferredLanguage();
        }

        return $this->redirectToRoute('home.index', ['_locale' => $locale]);
    }

    /**
     * index - Application main page with locale
     *
     * Route: home.index, /{_locale}/, method: GET
     *
     * @param Request $request
     *
     * @return Response
     */
    #[Route('/{_locale}/', name: 'home.index', methods: ['GET'], requirements: ["_locale" => "%app.localesString%"])]
    public function index(
        Request $request
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("HomeController::index()");
        }

        return $this->render('home/index.html.twig', [
            'currentMenu' => 'home'
        ]);
    }

    /**
     * support - Contact support page
     *
     * Route: home.support, /{_locale}/support, method: GET, POST
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param TranslatorInterface $translator
     * @param MailerService $mailer
     *
     * @return Response
     */
    #[Route('/{_locale}/support', name: 'home.support', methods: ['GET', 'POST'], requirements: ["_locale" => "%app.localesString%"])]
    public function support(
        Request $request,
        EntityManagerInterface $manager,
        TranslatorInterface $translator,
        MailerService $mailer
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("HomeController::support()");
        }

        $defaultUserName = '';
        $defaultUserEmail = '';

        if($this->getUser())
        {
            $defaultUserName = $this->getUser()->getName();
            $defaultUserEmail = $this->getUser()->getEmail();
        }

        $form = $this->createForm(SupportMessageType::class,
            [
                'name' => $defaultUserName,
                'email' => $defaultUserEmail,
            ]
        );
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $userId = false;
            $userName = $form->getData()['name'];
            $userEmail = $form->getData()['email'];

            $repository = $manager->getRepository(User::class);
            $user = $repository->findOneBy(['email' => $userEmail]);

            if($user)
            {
                $userId = $user->getId();
                $userName = $user->getName();
            }

            if(
                $mailer->sendSupportMessage(
                    $userId,
                    $userName,
                    $userEmail,
                    $form->getData()['subject'],
                    $form->getData()['message']
                )
            ) {

                $this->addFlash('info', $translator->trans('info.supportMessageSent'));
                return $this->redirectToRoute('home.index');

            } else {

                $this->logger->critical("HomeController::support() : Error sending e-mail");
                $this->addFlash('error', $translator->trans('error.emailNotSent'));
            }
        }

        return $this->render('home/support.html.twig', [
            'currentMenu' => 'home',
            'form' => $form->createView(),
        ]);
    }
}
