<?php

namespace App\EntityListener;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * UserListener - User listener
 *
 * @property LoggerInterface $logger
 * @property Bool $enableLog
 * @property UserPasswordHasherInterface $hasher
 */
class UserListener
{
    private $logger;
    private $enableLog;

    private UserPasswordHasherInterface $hasher;

    /**
     * Class constructor - Get system parameters and log tool
     *
     * @param UserPasswordHasherInterface $hasher
     * @param LoggerInterface $logger
     * @param ParameterBagInterface $params
     */
    public function __construct(
        UserPasswordHasherInterface $hasher,
        LoggerInterface $logger,
        ParameterBagInterface $params
    )
    {
        $this->logger = $logger;
        $this->enableLog = $params->get('app.enableLog');

        if($this->enableLog)
        {
            $this->logger->notice("UserListener::__construct()");
        }

        $this->hasher = $hasher;
    }

    /**
     * encodePassword - Set hashed password from clear password if exists
     *
     * @param User $user
     */
    public function encodePassword(User $user)
    {
        if($this->enableLog)
        {
            $this->logger->notice("UserListener::encodePassword()");
        }

        if(
            $user->getPlainPassword() === null
            ||
            $user->getPlainPassword() === ''
        )
        {
            return;
        }

        $user->setPassword(
            $this->hasher->hashPassword(
                $user,
                $user->getPlainPassword()
            )
        );

        $user->setPlainPassword('');
    }

    /**
     * prePersist - Pre-persist user action, encrypt password
     *
     * @param User $user
     */
    public function prePersist(User $user)
    {
        if($this->enableLog)
        {
            $this->logger->notice("UserListener::prePersist()");
        }
        $this->encodePassword($user);
    }

    /**
     * preUpdate - Pre-update user action, encrypt password
     *
     * @param User $user
     */
    public function preUpdate(User $user)
    {
        if($this->enableLog)
        {
            $this->logger->notice("UserListener::preUpdate()");
        }
        $this->encodePassword($user);
    }
}
