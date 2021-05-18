<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/post", name="post.")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->render('post/index.html.twig', [
            'controller_name' => 'PostController',
        ]);
    }

    /**
     * @Route("/create", name="create")
     * @return Response
     * @throws \Exception
     */
    public function create()
    {
        $post = new Post();

        $post
            ->setContent("Test")
            ->setCreatedAt(new \DateTime())
            ->setUserId($this->getUser());

        dump($post);

        $em = $this->getDoctrine()->getManager();
        $em->persist($post);
        // $em->flush();

        return new Response("Post was created.");
    }
}
