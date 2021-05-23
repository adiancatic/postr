<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExploreController extends AbstractController
{
    /**
     * @Route("/explore", name="explore")
     * @param PostRepository $postRepository
     * @return Response
     */
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAllWithAuthorsExceptCurrentUser();

        return $this->render('explore/index.html.twig', [
            "posts" => $posts,
        ]);
    }
}
