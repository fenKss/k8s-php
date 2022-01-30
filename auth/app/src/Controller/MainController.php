<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    /**
     * @Route("/auth/traefik", name="nginx")
     */
    public function main(Request $request): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if ($user){
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
}
