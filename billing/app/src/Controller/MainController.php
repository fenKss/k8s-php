<?php

namespace App\Controller;

use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main")
     */
    public function main(Request $request): Response
    {
        return $this->json($request->headers->all());
    }

    /**
     * @Route("/notifications", name="test")
     */
    public function test(
        Request $request,
        NotificationRepository $notificationRepository
    ): Response {
        $notifications = $notificationRepository->findAll();
        $responseData  = [];
        foreach ($notifications as $notification) {
            $responseData[] = [
                'id' => $notification->getId(),
                'message' => $notification->getMessage(),
                'user' => [
                    'id' => $notification->getUser()->getId(),
                    'token' => $notification->getUser()->getAuthToken(),
                ],
            ];
        }

        return $this->json($responseData);
    }
}