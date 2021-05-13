<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/u", name="profile.")
 */
class ProfileController extends AbstractController
{
    /**
     * @Route("/{username}", name="index")
     * @param $username
     * @param UserRepository $userRepository
     * @param PostRepository $postRepository
     * @return Response
     */
    public function index($username, UserRepository $userRepository, PostRepository $postRepository): Response
    {
        $user = $userRepository->findOneBy(["username" => $username]);
        if(!$user) {
            return new Response("404, not found", 404);
        }

        $posts = $postRepository->findByIdWithAuthors($user->getId());

        return $this->render("profile/index.html.twig", [
            "user" => $user,
            "posts" => $posts,
        ]);
    }
}
