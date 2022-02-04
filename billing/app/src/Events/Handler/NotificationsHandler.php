<?php

namespace App\Events\Handler;

use App\Events\Event;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class NotificationsHandler
{
    private UserRepository         $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserRepository $repository,
        EntityManagerInterface $entityManager
    ) {
        $this->repository    = $repository;
        $this->entityManager = $entityManager;
    }

    public function sendEvent(Event $event)
    {
        $userToken = $event->get('user_token');
        if (!$userToken) {
            return;
        }
        $user = $this->repository->findOneBy([
            'authToken' => $userToken,
        ]);
        if (!$user) {
            return;
        }
//        $user->setMoney();
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}