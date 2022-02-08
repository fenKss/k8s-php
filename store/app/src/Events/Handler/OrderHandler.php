<?php

namespace App\Events\Handler;

use App\Events\Event;
use App\Service\KafkaService;
use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;

class OrderHandler
{
    private UserRepository         $repository;
    private EntityManagerInterface $entityManager;
    private KafkaService           $kafkaService;
    /**
     * @var \App\Repository\OrderRepository
     */
    private OrderRepository $orderRepository;

    public function __construct(
        UserRepository $repository,
        EntityManagerInterface $entityManager,
        OrderRepository $orderRepository,
        KafkaService $kafkaService
    ) {
        $this->repository      = $repository;
        $this->entityManager   = $entityManager;
        $this->kafkaService    = $kafkaService;
        $this->orderRepository = $orderRepository;
    }


    public function handleOrderBillingEvent(Event $event)
    {
        $status  = $event->get('status') ?? false;
        $orderId = $event->get('order_id');
        if (!$orderId) {
            throw new \Exception('Order id not found');
        }
        $order = $this->orderRepository->find($orderId);
        if ($status) {
            $eventData = [
                '__event' => 'Send',
                'message' => "Order $orderId was processed",
                'user_token' => $order->getUser()->getAuthToken(),
            ];
            $this->kafkaService->send('notifications', json_encode($eventData), null);
        } else {
            throw new \Exception("Status is not true. Order id $orderId");
        }

    }
}