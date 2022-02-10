<?php

namespace App\Events\Handler;

use App\Events\Event;
use App\Service\KafkaService;
use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use App\Repository\CourierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Log\Logger;

class DeliveryHandler
{
    private EntityManagerInterface $entityManager;
    private KafkaService           $kafkaService;
    private Logger                 $logger;
    private CourierRepository      $repository;

    public function __construct(
        EntityManagerInterface $entityManager,
        CourierRepository $repository,
        KafkaService $kafkaService,
        Logger $logger
    ) {
        $this->entityManager = $entityManager;
        $this->kafkaService  = $kafkaService;
        $this->logger        = $logger;
        $this->repository    = $repository;
    }

    public function reserveCourierEvent(Event $event)
    {
        try {
            $orderId = $event->get('order_id');
            $productId = $event->get('product_id');
            if (!$orderId) {
                throw new \Exception("Property order_id not found");
            }
            if (!$productId) {
                throw new \Exception("Property order_id not found");
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
                "__event" => 'unreserveProductEvent',
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'product_id' => $productId,
            ];
            $this->kafkaService->send('store', json_encode($data), $orderId);
        }
    }
}