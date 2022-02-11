<?php

namespace App\Controller;

use App\Entity\Courier;
use App\Repository\CourierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/delivery", name="delivery.")
 */
class MainController extends AbstractController
{
    /**
     * @Route("/test", name="est")
     */
    public function test(CourierRepository $repository, EntityManagerInterface $entityManager): Response
    {
        $courier = $repository->getFirstFree();
        $courier->setIsReserved(true)->setOrderId(1);
        $entityManager->persist($courier);
        $entityManager->flush();
        return $this->json(['delivery']);
    }

    /**
     * @Route("/courier/list", name="courier.list")
     */
    public function list(CourierRepository $repository): Response
    {
        return $this->json($repository->findAll());
    }

    /**
     * @Route("/courier", name="courier.create", methods={"POST"})
     */
    public function create(EntityManagerInterface $entityManager): Response
    {
        $courier = (new Courier())->setIsReserved(false);
        $entityManager->persist($courier);
        $entityManager->flush();
        return $this->json($courier);
    }
}