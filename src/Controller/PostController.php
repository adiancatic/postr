<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
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
    protected $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Route("/create", name="create", methods={"POST"})
     * @param Request $request
     * @param PostRepository $postRepository
     * @return Response
     * @throws \Exception
     */
    public function create(Request $request, PostRepository $postRepository)
    {
        $params = [];
        parse_str($request->get("formData"), $params);

        if(!$params) return new Response("Invalid request", 400);
        $params = $params["post"];

        $post = new Post();

        $post
            ->setContent($params["content"])
            ->setCreatedAt(new \DateTime())
            ->setUserId($this->security->getUser());

        $em = $this->getDoctrine()->getManager();
        $em->persist($post);
        // $em->flush();

        $postData = $postRepository->getLast();

        if(!$postData) {
            return new Response("No records found", 400);
        }

        $return = $this->renderView("/post/item.html.twig", [
            "post" => $postData,
        ]);

        return new Response($return, 200);
    }
}
