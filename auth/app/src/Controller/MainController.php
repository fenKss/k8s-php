<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    private \App\Repository\UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    /**
     * @Route("/auth/traefik", name="nginx")
     */
    public function main(Request $request): Response
    {
        if ($user = $this->user($request)){
            $response = new Response();
            $response->headers->add([
                "x-auth-token" => $user->getAuthToken(),
                "x-username" => $user->getUserIdentifier(),
            ]);
            return $response;
        }

        $redirect_uri = $request->headers->get('x-forwarded-uri');
        $host = $request->headers->get('x-forwarded-host');
        $url = $request->getScheme()."://$host/auth/login?ru=$redirect_uri";
        return new RedirectResponse($url, Response::HTTP_FOUND);
    }

    private function user(Request $request) :?User
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($user){
            return $user;
        }
        $token = $request->cookies->get('xau', null);
        if (!$token){
            return null;
        }
        return $this->userRepository->findOneBy([
            'authToken' => $token
        ]);
    }

}
