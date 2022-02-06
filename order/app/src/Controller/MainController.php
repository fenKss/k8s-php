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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    /**
     * @Route("/order/list", name="orders")
     */
    public function orders(OrderRepository $repository): Response
    {
        $data   = [];
        $orders = $repository->findAll();
        foreach ($orders as $order) {
            $data[] = [
                'id' => $order->getId(),
                'money' => $order->getMoney(),
                'user' => [
                    'token' => $order->getUser()->getAuthToken(),
                ],
            ];
        }
        return $this->json($data);
    }

    /**
     * @Route("/order", name="asd")
     */
    public function create(
        Request $request,
        KafkaService $kafkaService,
        EntityManagerInterface $em
    ): Response {
        $money = (float)$request->get('money');
        if (!$money || $money <= 0) {
            return new Response("Bad Request", Response::HTTP_BAD_REQUEST);
        }
        /** @var User $user */
        $user  = $this->getUser();
        $order = (new Order())->setUser($user)->setMoney($money);
        $em->persist($order);
        $em->flush();
        $event = [
            '__event' => "moneyDecrease",
            'user_token' => $user->getAuthToken(),
            'money' => $money,
            'order_id' => $order->getId(),
        ];
        $event = json_encode($event);
        $kafkaService->send('billing', $event, $order->getId());
        return $this->json(['orders']);
    }

    /**
     * @Route("/order/test", name="est")
     */
    public function test(
        KafkaService $kafkaService
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $event = [
            '__event' => "Send",
            'message' => 'testasdasd',
            'auth_token'=> $user->getAuthToken()
        ];
        $event = json_encode($event);
        $kafkaService->send('notifications', $event, 1);
        return new Response();
    }
}