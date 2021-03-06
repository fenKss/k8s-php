<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\KafkaService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MoneyController extends AbstractController
{
    /**
     * @Route("/money/increase", methods={"GET", "POST"})
     */
    public function increase(Request $request, ManagerRegistry $doctrine): Response
    {
        $money = (float)$request->get('money');
        if (!$money) {
            return new Response("Bad Request", Response::HTTP_BAD_REQUEST);
        }
        /** @var User $user */
        $user = $this->getUser();
        $user->increaseMoney($money);
        $em = $doctrine->getManager();
        $em->persist($user);
        $em->flush();
        return new Response();
    }

    /**
     * @Route("/money/decrease", methods={"GET", "POST"})
     */
    public function decrease(Request $request, ManagerRegistry $doctrine): Response
    {
        $money = (float)$request->get('money');
        if (!$money) {
            return new Response("Bad Request", Response::HTTP_BAD_REQUEST);
        }
        try {
            /** @var User $user */
            $user = $this->getUser();
            $user->decreaseMoney($money);
            $em = $doctrine->getManager();
            $em->persist($user);
            $em->flush();
            return new Response();
        } catch (\LogicException $e) {
            return new Response("Bad Request", Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/money", name="checkMoney")
     */
    public function check( ): Response
    {
       return $this->json(['money' => $this->getUser()->getMoney()]);
    }
    /**
     * @Route("/money/test", name="tests")
     */
    public function test(KafkaService $kafkaService): Response
    {
        $orderId = 1;
        $event = [
            "__event" => "HandleOrder",
            "order_id" => $orderId,
            'status' => 0,
        ];
        $kafkaService->send('order', json_encode($event), $orderId);
        return new Response('sent');
    }


}