<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class MainController extends AbstractController
{
    protected $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Route("/", name="home")
     * @param PostRepository $postRepository
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function index(PostRepository $postRepository, Request $request): Response
    {
        $newPost = new Post();

        $newPost
            ->setCreatedAt(new \DateTime())
            ->setUserId($this->getUser())
        ;

        $form = $this->createForm(PostType::class, $newPost);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($newPost);
            $em->flush();
        }

        if($this->security->getUser()) {
            $posts = $postRepository->getPostsFromFollowedUsers($this->security->getUser()->getId());
        } else {
            $posts = $postRepository->findAllWithAuthors();
        }

        return $this->render('main/index.html.twig', [
            "newPost" => $form->createView(),
            'posts' => $posts,
        ]);
    }
}
