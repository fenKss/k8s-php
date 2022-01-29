<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main")
     */
    public function main(UserRepository $userRepository): \Symfony\Component\HttpFoundation\JsonResponse
    {

        return $this->json($userRepository->findAll());
    }

    /**
     * @Route("/health", name="health")
     */
    public function health(): Response
    {
        return new Response('ok');
    }
}