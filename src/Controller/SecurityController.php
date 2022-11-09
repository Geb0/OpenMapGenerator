<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\User;
use App\Form\RegistrationType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Form\UserLostPasswordType;
use App\Form\UserResetPasswordType;
use App\Service\MailerService;

/**
 * SecurityController - Application security administration
 *
 * @property Boolean $enableLog Application log enabled or not
 * @property LoggerInterface $logger Application log interface
 *
 * @property Integer $newPasswordLength Password minimum length
 *
 * @method Response userConnect() User connection
 * @method Response userConnected() User connected with his language
 * @method Response userDisconnect() Disconnect user
 * @method Response userRegistration() Register new user
 * @method Response lostPassword() User lost password
 * @method Response resetPassword() Reset user password
 */
class SecurityController extends AbstractController
{
    private $enableLog;
    private $logger;

    private $newPasswordLength;

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
            $this->logger->notice("SecurityController::__construct()");
        }

        $this->newPasswordLength = $params->get('app.passwordMinLength');
    }

    /**
     * userConnect - User connection
     *
     * Route: security.connect, /connect, method: GET, POST
     *
     * @param AuthenticationUtils $authenticationUtils
     *
     * @return Response
     */
    #[Route('/{_locale}/connect', name: 'security.connect', methods: ['GET', 'POST'], requirements: ["_locale" => "%app.localesString%"])]
    public function userConnect(
        AuthenticationUtils $authenticationUtils
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("SecurityController::userConnect()");
        }

        return $this->render('security/connect.html.twig', [
            'currentMenu' => 'connect',
            'last_username' => $authenticationUtils->getLastUserName(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * userConnected - User connected with his language
     *
     * Route: security.connected, /connect, method: GET
     *
     * @param AuthenticationUtils $authenticationUtils
     *
     * @return Response
     */
    #[Route('/{_locale}/connected', name: 'security.connected', methods: ['GET'], requirements: ["_locale" => "%app.localesString%"])]
    public function userConnected(
        AuthenticationUtils $authenticationUtils
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("SecurityController::userConnected()");
        }

        // Use user language to show page
        return $this->redirectToRoute(
            'map.manage',
            ['_locale' => $this->getUser()->getLanguage()]
        );
    }

    /**
     * userDisconnect - Disconnect user
     *
     * Route: security.disconnect, /disconnect, method: GET
     */
    #[Route('/{_locale}/disconnect', name: 'security.disconnect', methods: ['GET'], requirements: ["_locale" => "%app.localesString%"])]
    public function userDisconnect() {

        if($this->enableLog)
        {
            $this->logger->notice("SecurityController::userDisconnect()");
        }
    }

    /**
     * userRegistration - Register new user
     *
     * Route: security.registration, /registration, method: GET, POST
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    #[Route('/{_locale}/registration', name: 'security.registration', methods: ['GET', 'POST'], requirements: ["_locale" => "%app.localesString%"])]
    public function userRegistration(
        Request $request,
        EntityManagerInterface $manager,
        TranslatorInterface $translator
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("SecurityController::userRegistration()");
        }

        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $user = $form->getData();
            $user->setLanguage($request->request->get('language'));
            $user->setRoles(['ROLE_USER']);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('info', $translator->trans('info.accountCreated'));
            return $this->redirectToRoute('security.connect');
        }

        return $this->render('security/registration.html.twig', [
            'currentMenu' => 'connect',
            'form' => $form->createView(),
        ]);
    }

    /**
     * lostPassword - User lost password
     *
     * Route: security.lostpassword, /lostpassword, method: GET, POST
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param TranslatorInterface $translator
     * @param MailerService $mailer
     *
     * @return Response
     */
    #[Route('/{_locale}/lostpassword', name: 'security.lostpassword', methods: ['GET', 'POST'], requirements: ["_locale" => "%app.localesString%"])]
    public function lostPassword(
        Request $request,
        EntityManagerInterface $manager,
        TranslatorInterface $translator,
        MailerService $mailer
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("SecurityController::lostPassword()");
        }

        $form = $this->createForm(UserLostPasswordType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $repository = $manager->getRepository(User::class);
            $userEmail = $form->getData()['email'];
            $user = $repository->findOneBy(['email' => $userEmail]);

            if($user)
            {
                // Generate recover key

                $string = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
                $recoverKey = '';

                for($i = 0; $i < 20; $i++)
                {
                    $recoverKey .= substr($string, rand(0, strlen($string) - 1), 1);
                }

                // Send e-mail

                if(
                    $mailer->sendLostPassword(
                        $user,
                        $recoverKey
                    )
                ) {

                    // Save recover key

                    $user->setRecoverkey($recoverKey);
                    $manager->persist($user);
                    $manager->flush();
                    $this->addFlash('info', $translator->trans('info.recoverPasswordSend'));
                    return $this->redirectToRoute('security.connect');

                } else {

                    $this->logger->critical("SecurityController::lostPassword() : Error sending e-mail");
                    $this->addFlash('error', $translator->trans('error.emailNotSent'));
                }
            }
        }
        return $this->render('security/lostpassword.html.twig', [
            'currentMenu' => 'user',
            'form' => $form->createView(),
        ]);
    }

    /**
     * resetPassword - Reset user password
     *
     * Route: security.resetpassword, /resetpassword, method: GET, POST
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    #[Route('/{_locale}/resetpassword/{key}', name: 'security.resetpassword', methods: ['GET', 'POST'], requirements: ["_locale" => "%app.localesString%"])]
    public function resetPassword(
        Request $request,
        EntityManagerInterface $manager,
        TranslatorInterface $translator
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("SecurityController::resetPassword()");
        }

        $routeParams = $request->attributes->get('_route_params');
        $key = $routeParams['key'];

        if($key === '')
        {
            return $this->redirectToRoute('security.connect');
        }

        $repository = $manager->getRepository(User::class);
        $user = $repository->findOneBy(['recoverkey' => $key]);

        if(!$user)
        {
            return $this->redirectToRoute('security.connect');
        }

        $form = $this->createForm(UserResetPasswordType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            // Save new password

            $user->setPlainPassword($form->getData()['newPassword']);
            $user->setRecoverkey('');
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('info', $translator->trans('info.resetPasswordDone'));
            return $this->redirectToRoute('security.connect');
        }

        return $this->render('security/resetpassword.html.twig', [
            'currentMenu' => 'user',
            'form' => $form->createView(),
        ]);
    }
}
