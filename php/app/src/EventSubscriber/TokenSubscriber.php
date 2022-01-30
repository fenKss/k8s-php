<?php

namespace App\EventSubscriber;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class TokenSubscriber implements EventSubscriberInterface
{
    private $tokens;    private $stack;
    public function __construct(RequestStack $requestStack)
    {
        $this->stack = $requestStack;
    }

    public function onKernelController(ControllerEvent $event)
    {
//        $controller = $event->getController();
//        dd($event->getRequest()->getSession()->all(), $this->stack->getCurrentRequest() );
//        throw new AccessDeniedHttpException('This action needs a valid token!');

        // when a controller class defines multiple action methods, the controller
        // is returned as [$controllerInstance, 'methodName']
//        if (is_array($controller)) {
//            $controller = $controller[0];
//        }

//        if ($controller instanceof TokenAuthenticatedController) {
//            $token = $event->getRequest()->query->get('token');
//            if (!in_array($token, $this->tokens)) {
//                throw new AccessDeniedHttpException('This action needs a valid token!');
//            }
//        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}