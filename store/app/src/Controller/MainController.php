<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Order;
use App\Entity\Product;
use App\Enum\EProductStatus;
use App\Service\KafkaService;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/store", name="store.")
 */
class MainController extends AbstractController
{
    /**
     * @Route("/test", name="test")
     */
    public function test(): JsonResponse
    {
        return $this->json(['store']);
    }

    /**
     * @Route("/product/list", name="list")
     */
    public function list(ProductRepository $productRepository
    ): JsonResponse {
        return $this->json($productRepository->findAll());
    }

    /**
     * @Route("/product/create", name="create")
     */
    public function create(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $name = $request->get('name');
        if (!$name) {
            return $this->json(['error' => 'invalid property name'],
                Response::HTTP_BAD_REQUEST);
        }
        $product = (new Product())->setName($name)
                                  ->setStatus((new EProductStatus(EProductStatus::FREE)));
        $entityManager->persist($product);
        $entityManager->flush();
        return $this->json($product);
    }
}