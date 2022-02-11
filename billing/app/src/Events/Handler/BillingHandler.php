<?php

namespace App\Events\Handler;

use App\Entity\User;
use App\Events\Event;
use App\Service\UserService;
use App\Service\KafkaService;
use App\Repository\UserRepository;
use App\Repository\OrderRepository;
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
    public function orderCreateEvent(Event $event)
    {
        $userToken = $event->get('user_token');
        $orderId   = $event->get('order_id');
        $productId = $event->get('product_id');
        $topic     = 'store';
        if (!$userToken) {
            throw new \Exception('User Token not found');
        }
        $money = $event->get('money');
        if (!$money || $money <= 0) {
            throw new \Exception("Invalid money $money");
        }
        $user         = $this->userService->getUser($userToken);
        $currentMoney = $event->get('current_money');
        $event        = [
            "__event" => "ReserveProduct",
            "order_id" => $orderId,
            "product_id" => $productId,
            'status' => 1,
            "money" => $event->get('money'),
            "user_token" => $event->get('user_token'),
        ];
        try {

            if (!is_null($currentMoney) &&
                (float)$user->getMoney() !== (float)$currentMoney) {
                throw new \Exception("Message already processed".
                                     (float)$user->getMoney().
                                     " ".
                                     (float)$currentMoney);
            }
            $user->decreaseMoney($money);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            $event['__event'] = "HandleOrder";
            $event['status']  = 0;
            $event['reason']  = $e->getMessage();
            $topic            = 'order';
        } finally {
            $this->kafkaService->send($topic, json_encode($event), $orderId);
        }
    }

    public function increaseMoneyEvent(Event $event)
    {
        $userToken = $event->get('user_token');
        $orderId   = $event->get('order_id');
        $productId = $event->get('product_id');
        if (!$userToken) {
            throw new \Exception('User Token not found');
        }
        $money = $event->get('money');
        if (!$money || $money <= 0) {
            throw new \Exception("Invalid money $money");
        }
        $user  = $this->userService->getUser($userToken);
        $event = [
            "__event" => "HandleOrder",
            "order_id" => $orderId,
            "product_id" => $productId,
            'status' => 0,
            "money" => $event->get('money'),
            "user_token" => $event->get('user_token'),
        ];

        $user->increaseMoney($money);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->kafkaService->send('order', json_encode($event), $orderId);
    }
}