<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Form\UserType;
use App\Form\UserPasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * UserController - User administration
 *
 * Security: is_granted('ROLE_USER') and user === transmittedUser
 *
 * @property Boolean $enableLog Application log enabled or not
 * @property LoggerInterface $logger Application log interface
 *
 * @property Array $locales Available application locales
 *
 * @method Response userUpdate() Update user account parameters
 * @method Response userUpdatePassword() Update user account password
 */
#[Security("is_granted('ROLE_USER') and user === transmittedUser")]
class UserController extends AbstractController
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
        ParameterBagInterface $params
    )
    {
        $this->logger = $logger;
        $this->enableLog = $params->get('app.enableLog');

        if($this->enableLog)
        {
            $this->logger->notice("UserController::__construct()");
        }

        $this->locales = $params->get('app.locales');
    }

    /**
     * userUpdate - Update user account parameters
     *
     * Route: user.update, /user/update/{id}, method: GET, POST
     *
     * @param User $transmittedUser
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    #[Route('/{_locale}/user/update/{id}', name: 'user.update', methods: ['GET', 'POST'], requirements: ["_locale" => "%app.localesString%"])]
    public function userUpdate(
        User $transmittedUser,
        Request $request,
        EntityManagerInterface $manager,
        TranslatorInterface $translator
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("UserController::userUpdate()");
        }

        $form = $this->createForm(UserType::class, $transmittedUser);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $transmittedUser = $form->getData();
            $manager->persist($transmittedUser);
            $manager->flush();
            $this->addFlash('info', $translator->trans('info.accountUpdated'));
            return $this->redirectToRoute('user.update', [
                '_locale' => $transmittedUser->getLanguage(),
                'id' => $transmittedUser->getId(),
            ]);
        }

        return $this->render('user/update.html.twig', [
            'currentMenu' => 'user',
            'currentLanguage' => $transmittedUser->getLanguage(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * userUpdatePassword - Update user account password
     *
     * Route: user.update.password, /user/update.password/{id}, method: GET, POST
     *
     * @param User $transmittedUser
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordHasherInterface $hasher
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    #[Route('/{_locale}/user/update.password/{id}', name: 'user.update.password', methods: ['GET', 'POST'], requirements: ["_locale" => "%app.localesString%"])]
    public function userUpdatePassword(
        User $transmittedUser,
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $hasher,
        TranslatorInterface $translator
    ): Response
    {
        if($this->enableLog)
        {
            $this->logger->notice("UserController::userUpdatePassword()");
        }

        $form = $this->createForm(UserPasswordType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            // Actual password control
            if($hasher->isPasswordValid($transmittedUser, $form->getData()['actualPassword']))
            {
                $transmittedUser->setPlainPassword($form->getData()['newPassword']);

                // /!\ Important
                //
                //   Because plainPassword is a virtual field that not
                //   exists in DB table but only in the User entity,
                //   preUpdate will not be executed and SQL UPDATE
                //   command will not be send to database.
                //
                //   To force update, change the value of a real table
                //   field like updated_at here.

                $transmittedUser->setUpdatedAt(new \DateTimeImmutable);

                $manager->persist($transmittedUser);
                $manager->flush();
                $this->addFlash('info', $translator->trans('info.passwordUpdated'));
                return $this->redirectToRoute('user.update', ['id' => $this->getUser()->getId()]);

            } else {

                $this->addFlash('error', $translator->trans('error.actualPassword'));
            }
        }

        return $this->render('user/update.password.html.twig', [
            'currentMenu' => 'user',
            'form' => $form->createView(),
        ]);
    }
}
