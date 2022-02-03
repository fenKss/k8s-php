<?php

namespace App\Events\Handler;

use App\Events\Event;
use App\Entity\Notification;
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
        $notification = new Notification();
        $notification->setMessage($event->get('message'))->setUser($user);
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}