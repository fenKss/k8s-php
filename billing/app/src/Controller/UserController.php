<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("s", name="user_index", methods={"GET", "POST"})
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        $authUser = $this->getUser();
        if ($authUser->getId() !== $user->getId()) {
            return new Response("Forbidden", Response::HTTP_FORBIDDEN);
        }
        return $this->json([
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET", "POST"})
     */
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        $authUser = $this->getUser();
        if ($authUser->getId() !== $user->getId()) {
            return new Response("Forbidden", Response::HTTP_FORBIDDEN);
        }
        $username = $request->get('firstName');
        $user->setFirstName($username);
        $entityManager->persist($user);
        $entityManager->flush();
        return $this->redirectToRoute('user_show', [
            'id' => $user->getId()
        ]);
    }

    /**
     * @Route("/", name="get_user", methods={"GET", "POST"})
     */
    public function getInfo(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $authUser = $this->getUser();
        if (!$authUser) {
            return new Response("", Response::HTTP_UNAUTHORIZED);
        }
        return $this->json([
            'id' => $authUser->getId(),
            'firstName' => $authUser->getFirstName(),
        ]);
    }
}
