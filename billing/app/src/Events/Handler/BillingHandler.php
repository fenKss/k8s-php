<?php

namespace App\Events\Handler;

use App\Entity\User;
use App\Events\Event;
use App\Service\UserService;
use App\Service\KafkaService;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class BillingHandler
{
    private UserService            $userService;
    private EntityManagerInterface $entityManager;
    private KafkaService           $kafkaService;

    public function __construct(
        UserService $userService,
        EntityManagerInterface $entityManager,
        KafkaService $kafkaService
    ) {
        $this->userService   = $userService;
        $this->entityManager = $entityManager;
        $this->kafkaService  = $kafkaService;
    }

    /**
     * @throws \Exception
     */
    public function moneyDecreaseEvent(Event $event)
    {
        $userToken = $event->get('user_token');
        $orderId   = $event->get('order_id');
        if (!$userToken) {
            throw new \Exception('User Token not found');
        }
        $money = $event->get('money');
        if (!$money || $money <= 0) {
            throw new \Exception("Invalid money $money");
        }
        $user  = $this->userService->getUser($userToken);
        $event = [
            "__event" => "HandleOrderBilling",
            "order_id" => $orderId,
            'status' => 1,
        ];

        try {
            $user->decreaseMoney($money);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            $event['status'] = 0;
        } finally {
            $this->kafkaService->send('order', json_encode($event), $orderId);
        }

    }
}