<?php
namespace App\Controller;
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
     * @Route("/test", name="test")
     */
    public function test(Request $request): Response
    {
        return $this->json($request->headers->all());
    }
}