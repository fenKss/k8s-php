<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Order;
use App\Service\KafkaService;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    /**
     * @Route("/delivery/test", name="est")
     */
    public function test(
    ): Response {
        return $this->json(['delivery']);
    }

}