<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ExploreController extends AbstractController
{
    /**
     * @Route("/explore", name="explore")
     * @param PostRepository $postRepository
     * @param UserRepository $userRepository
     * @param Security $security
     * @return Response
     */
    public function index(PostRepository $postRepository, UserRepository $userRepository, Security $security): Response
    {
        if(!$security->getUser()) {
            return $this->redirectToRoute("app_login");
        }

        $posts = $postRepository->findAllWithAuthorsExceptCurrentUser();

        $users = $userRepository->getUsersYouDontFollow();

        return $this->render('explore/index.html.twig', [
            "posts" => $posts,
            "users" => $users,
        ]);
    }
}
