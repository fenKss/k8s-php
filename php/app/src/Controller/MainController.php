<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main")
     */
    public function main(): \Symfony\Component\HttpFoundation\JsonResponse
    {
        return $this->json(['123']);
    }

    /**
     * @Route("/health", name="health")
     */
    public function health(): Response
    {
        return new Response('ok');
    }
}