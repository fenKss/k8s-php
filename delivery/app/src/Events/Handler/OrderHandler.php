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
    private OrderRepository        $orderRepository;

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
}