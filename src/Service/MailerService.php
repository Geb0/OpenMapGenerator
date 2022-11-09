<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use App\Entity\User;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

use App\Service\SupportTypesService;

/**
 * MailerService - Mail service administration using PHPMailer
 *
 * @property Boolean $enableLog Application log enabled or not
 * @property LoggerInterface $logger Application log interface
 * @property TranslatorInterface $translator
 *
 * @property SupportTypesService $supportTypes Types for support messages
 * @property String $supportEmail Email address from for support messages
 *
 * @property String $emailHost SMTP host
 * @property String $emailPort SMTP port
 * @property String $emailUser SMTP connection user
 * @property String $emailPass SMTP connection password
 * @property String $emailFrom SMTP from e-mail
 *
 * @method Boolean sendLostPassword() Send recover password e-mail
 * @method Boolean sendSupportMessage() Send e-mail to support
 */
class MailerService extends AbstractController
{
    private $enableLog;
    private $logger;
    private $translator;

    private $supportTypes;
    private $supportEmail;

    private $emailHost;
    private $emailPort;
    private $emailUser;
    private $emailPass;
    private $emailFrom;

    /**
     * Class constructor - Get system parameters and log tool
     *
     * @param LoggerInterface $logger Application log interface
     * @param ParameterBagInterface $params Bag to get system parameters
     * @param TranslatorInterface $translator
     * @param SupportTypesService $supportTypes
     */
    public function __construct(
        LoggerInterface $logger,
        ParameterBagInterface $params,
        TranslatorInterface $translator,
        SupportTypesService $supportTypes,
    ) {

        $this->logger = $logger;
        $this->enableLog = $params->get('app.enableLog');

        if($this->enableLog)
        {
            $this->logger->notice("MailerService::__construct()");
        }

        $this->translator = $translator;
        $this->supportTypes = $supportTypes;

        $this->supportEmail = $params->get('app.supportEmail');

        $this->emailHost = $params->get('app.emailHost');
        $this->emailPort = $params->get('app.emailPort');
        $this->emailUser = $params->get('app.emailUser');
        $this->emailPass = $params->get('app.emailPass');
        $this->emailFrom = $params->get('app.emailFrom');
    }

    /**
     * sendLostPassword - Send recover password e-mail
     *
     * @param User $user User entity
     * @param String $recoverKey Recover key to reset password
     *
     * @return Boolean E-mail sent or not
     */
    public function sendLostPassword(
        User $user,
        String $recoverKey
    ): Bool
    {
        if($this->enableLog)
        {
            $this->logger->notice("MailerService::sendLostPassword()");
        }

        $subject = $this->translator->trans(
            'email.recoverSubject',
            [],
            'messages',
            $user->getLanguage()
        );

        $htmlMessage = $this->render('emails/recoverPassword.'.$user->getLanguage().'.html.twig',
            [
                'user' => $user,
                'recoverKey' => $recoverKey,
            ]
        )->getContent();

        $textMessage = $this->render('emails/recoverPassword.'.$user->getLanguage().'.text.twig',
            [
                'user' => $user,
                'recoverKey' => $recoverKey,
            ]
        )->getContent();

        $mail = new PHPMailer(true);

        try
        {
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = $this->emailHost;
            $mail->Port = $this->emailPort;
            $mail->SMTPAuth = true;
            $mail->Username = $this->emailUser;
            $mail->Password = $this->emailPass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

            $mail->WordWrap = 60;
            $mail->Priority = 1;
            $mail->CharSet = "UTF-8";
            $mail->isHTML(true);

            $mail->setFrom($this->emailFrom, 'OMG Password recovering service');
            $mail->addAddress($user->getEmail(), $user->getName());
            $mail->addReplyTo('no-reply@croue.com', 'No reply');

            $mail->Subject = $subject;
            $mail->Body = $htmlMessage;
            $mail->AltBody = $textMessage;
            $mail->AddEmbeddedImage("favicon.png", "logo", "favicon.png");

            $mail->send();

            $return = true;

        } catch (Exception $e) {

            $this->logger->critical('PHPMAILER ERROR: '.$mail->ErrorInfo);

            $return = false;
        }
        return $return;
    }

    /**
     * sendSupportMessage - Send e-mail to support
     *
     * @param Integer $userId User identifier or false if not
     * @param String $userName User name
     * @param String $userEmail User e-mail
     * @param String $userSubject Message subject
     * @param String $userMessage Message sent by user
     *
     * @return Boolean E-mail sent or not
     */
    public function sendSupportMessage(
        Integer $userId,
        String $userName,
        String $userEmail,
        String $messageSubject,
        String $messageContent
    ): Bool
    {
        if($this->enableLog)
        {
            $this->logger->notice("MailerService::sendSupportMessage()");
        }

        $subject = "[OMG support] message from {$userName}";
        $emailDate = new \DateTimeImmutable();

        $htmlMessage = $this->render('emails/supportMessage.html.twig',
            [
                'messageDate' => $emailDate,
                'userId' => $userId,
                'userName' => $userName,
                'userEmail' => $userEmail,
                'messageSubject' => $this->supportTypes->getLabel($messageSubject),
                'messageContent' => explode("\n", $messageContent),
            ]
        )->getContent();

        $textMessage = $this->render('emails/supportMessage.text.twig',
            [
                'messageDate' => $emailDate,
                'userId' => $userId,
                'userName' => $userName,
                'userEmail' => $userEmail,
                'messageSubject' => $this->supportTypes->getLabel($messageSubject),
                'messageContent' => $messageContent,
            ]
        )->getContent();

        $mail = new PHPMailer(true);

        try
        {
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = $this->emailHost;
            $mail->Port = $this->emailPort;
            $mail->SMTPAuth = true;
            $mail->Username = $this->emailUser;
            $mail->Password = $this->emailPass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

            $mail->WordWrap = 60;
            $mail->Priority = 1;
            $mail->CharSet = "UTF-8";
            $mail->isHTML(true);

            $mail->setFrom($this->emailFrom, 'OMG e-mail support service');
            $mail->addAddress($this->supportEmail);
            $mail->addReplyTo($userEmail, $userName);

            $mail->Subject = $subject;
            $mail->Body = $htmlMessage;
            $mail->AltBody = $textMessage;

            $mail->send();

            $return = true;

        } catch (Exception $e) {

            $this->logger->critical('PHPMAILER ERROR: '.$mail->ErrorInfo);

            $return = false;
        }
        return $return;
    }
}
