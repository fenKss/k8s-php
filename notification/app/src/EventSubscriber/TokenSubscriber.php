<?php

namespace App\EventSubscriber;

use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class TokenSubscriber implements EventSubscriberInterface
{
    private UserRepository $repository;
    private ContainerInterface $container;
    public function __construct(UserRepository $userRepository, ContainerInterface $container)
    {
        $this->repository = $userRepository;
        $this->container = $container;
    }

    public function onKernelController(ControllerEvent $event)
    {
        /** @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage $tokenStorage */
        $tokenStorage = $this->container->get("security.token_storage");
        if ($tokenStorage->getToken() && $tokenStorage->getToken()->getUser()) {
            return;
        }
        $token = $event->getRequest()->headers->get('x-auth-token');
        $user = $this->repository->findOneBy([
            'authToken' => $token
        ]);
        if (!$user) {
            return;
        }
        $token = new UsernamePasswordToken($user, $user->getPassword(), $user->getRoles());
        $tokenStorage->setToken($token);
        /** @var \Symfony\Component\EventDispatcher\EventDispatcher $eventDispatcher */
        $eventDispatcher =  $this->container->get("event_dispatcher");
        $eventDispatcher->dispatch(new InteractiveLoginEvent($event->getRequest(), $token),"security.interactive_login");
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}