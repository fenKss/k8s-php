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
        EntityManagerInterface $em,
        HttpClientInterface $client
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
        $currentMoney = $this->getMoney($request, $client);
        $event = [
            '__event' => "moneyDecrease",
            'user_token' => $user->getAuthToken(),
            'money' => $money,
            'order_id' => $order->getId(),
        ];
        if ($currentMoney){
            $event['current_money'] = $currentMoney;
        }
        $event = json_encode($event);
        $kafkaService->send('billing', $event, $order->getId());
        return $this->redirectToRoute('est');
    }

    /**
     * @Route("/order/test", name="est")
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function test(
        KafkaService $kafkaService,
        Request $request,
        HttpClientInterface $client
    ): Response {
        return $this->render('order.html.twig');
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \Symfony\Contracts\HttpClient\HttpClientInterface  $client
     *
     * @return mixed
     */
    private function getMoney(Request $request, HttpClientInterface $client)
    {
        $xau      = $request->cookies->get('xau');
        $client   = $client->withOptions([
            'headers' => [
                'x-auth-token' => $this->getUser()->getAuthToken(),
                'Cookie' => "xau=$xau"
            ],

        ]);
        $scheme   = $request->headers->get('x-forwarded-scheme');
        $host     = $request->headers->get('x-forwarded-host');
        $response = $client->request('GET', "$scheme://$host/money");

        try {
            return json_decode($response->getContent(), true)['money'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

}