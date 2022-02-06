<?php

namespace App\Events\Handler;

use App\Events\Event;
use App\Entity\Notification;
use App\Service\UserService;
use App\Service\KafkaService;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class NotificationsHandler
{
    private UserService            $userService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserService $userService,
        EntityManagerInterface $entityManager
    ) {
        $this->userService   = $userService;
        $this->entityManager = $entityManager;
    }

    public function sendEvent(Event $event)
    {
        $userToken = $event->get('user_token');
        if (!$userToken) {
            throw new \Exception("User token not found");
        }
        $user = $this->userService->getUser($userToken);
        $notification = new Notification();
        $notification->setMessage($event->get('message'))->setUser($user);
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}