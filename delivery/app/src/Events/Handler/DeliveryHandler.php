<?php

namespace App\Events\Handler;

use App\Events\Event;
use Psr\Log\LoggerInterface;
use App\Service\KafkaService;
use App\Repository\CourierRepository;
use Doctrine\ORM\EntityManagerInterface;

class DeliveryHandler
{
    private EntityManagerInterface $entityManager;
    private KafkaService           $kafkaService;
    private LoggerInterface        $logger;
    private CourierRepository      $repository;

    public function __construct(
        EntityManagerInterface $entityManager,
        CourierRepository $repository,
        KafkaService $kafkaService,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->kafkaService  = $kafkaService;
        $this->logger        = $logger;
        $this->repository    = $repository;
    }

    public function reserveCourierEvent(Event $event)
    {
        try {
            $orderId   = $event->get('order_id');
            $productId = $event->get('product_id');
            if (!$orderId) {
                throw new \Exception("Property order_id not found");
            }
            if (!$productId) {
                throw new \Exception("Property product_id not found");
            }
            $courier = $this->repository->getFirstFree();
            if (!$courier) {
                throw new \Exception('Free courier not found');
            }
            $courier->setIsReserved(true)->setOrderId($orderId);
            $this->entityManager->persist($courier);
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            $data = [
                "__event" => 'unreserveProduct',
                'error' => $e->getMessage(). $e->getFile(). $e->getLine(),
                'order_id' => $orderId,
                'product_id' => $productId,
                "money" => $event->get('money'),
                "user_token" => $event->get('user_token'),
            ];
            $this->kafkaService->send('store', json_encode($data), $orderId);
        }
    }
}