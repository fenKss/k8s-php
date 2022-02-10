<?php

namespace App\Events\Handler;

use App\Events\Event;
use App\Enum\EProductStatus;
use App\Service\KafkaService;
use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Log\Logger;

class StoreHandler
{
    private ProductRepository      $repository;
    private EntityManagerInterface $entityManager;
    private KafkaService           $kafkaService;
    private Logger           $logger;

    public function __construct(
        ProductRepository $repository,
        EntityManagerInterface $entityManager,
        KafkaService $kafkaService,
        Logger $logger
    ) {
        $this->repository    = $repository;
        $this->entityManager = $entityManager;
        $this->kafkaService  = $kafkaService;
        $this->logger  = $logger;
    }

    public function reserveProductEvent(Event $event)
    {
        try {
            $productId = $event->get('product_id');
            $orderId   = $event->get('order_id');
            $product   = $this->repository->find($productId);
            if (!$orderId){
                throw new \Exception("Property order_id not found");
            }
            if (!$product) {
                throw new \Exception("Product $productId not found");
            }
            if ($product->getStatus()->getValue() == EProductStatus::RESERVED) {
                throw new \Exception("Product $productId already reserved");
            }
            $product->setStatus(new EProductStatus(EProductStatus::RESERVED));
            $this->entityManager->persist($product);
            $this->entityManager->flush();
            $data = [
                "__event" => 'ReserveCourier',
                "order_id" => $orderId,
            ];
            $this->kafkaService->send('delivery', json_encode($data), $orderId);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            $data = [
                "__event" => 'RejectOrder',
                'error' => $e->getMessage(),
                'order_id' => $orderId,
            ];
            $this->kafkaService->send('billing', json_encode($data), $orderId);
        }
    }

    public function unreserveProductEvent(Event $event)
    {
        try {
            $productId = $event->get('product_id');
            $orderId   = $event->get('order_id');
            $product   = $this->repository->find($productId);
            if (!$orderId){
                throw new \Exception("Property order_id not found");
            }
            if (!$product) {
                throw new \Exception("Product $productId not found");
            }
            $product->setStatus(new EProductStatus(EProductStatus::FREE));
            $this->entityManager->persist($product);
            $this->entityManager->flush();
            $data = [
                "__event" => 'RejectOrder',
                'order_id' => $orderId,
            ];
            $this->kafkaService->send('billing', json_encode($data), $orderId);
        } catch (\Throwable $e) {
                $this->logger->error($e->getMessage());
//            $data = [
//                "__event" => 'RejectOrder',
//                'error' => $e->getMessage(),
//                'order_id' => $orderId,
//            ];
//            $this->kafkaService->send('billing', json_encode($data), $orderId);
        }
    }

}