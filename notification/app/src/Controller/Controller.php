<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Controller extends AbstractController
{
    private ?\Symfony\Component\HttpFoundation\Request $request;
    /**
     * @var \App\Repository\UserRepository
     */
    private UserRepository $userRepository;

    public function __construct(
        RequestStack $requestStack,
        UserRepository $userRepository
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->userRepository = $userRepository;
    }

    public function getUser(): ?User
    {
        return parent::getUser();
//        $token = $this->request->headers->get('x-auth-token');
//        if (!$token) {
//            return null;
//        }
//        return $this->userRepository->findOneBy([
//            'authToken' => $token,
//        ]);
    }
}