<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        if(!$params || !empty($params["content"])) return new Response("Invalid request", 400);
        $params = $params["post"];

        $post = new Post();

        $post
            ->setContent($params["content"])
            ->setCreatedAt(new \DateTime())
            ->setUserId($this->security->getUser());

        $em = $this->getDoctrine()->getManager();
        $em->persist($post);
        $em->flush();

        $postData = $postRepository->getLast();

        if(!$postData) {
            return new Response("No records found", 400);
        }

        $return = $this->renderView("/post/item.html.twig", [
            "post" => $postData,
        ]);

        return new Response($return, 200);
    }

    /**
     * @Route("/update", name="update", methods={"PUT"})
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function update(Request $request) {
        $params = [];
        parse_str($request->get("formData"), $params);

        if(!$params || empty($params["content"])) return new Response("Invalid request", 400);

        $em = $this->getDoctrine()->getManager();

        $post = $em->getRepository(Post::class)->find($params["postId"]);

        if(!$post) {
            throw $this->createNotFoundException('No post found for id ' . $params["postId"]);
        }

        $post->setContent($params["content"]);

        $em->flush();

        return new Response("Post successfully updated", 200);
    }

    /**
     * @Route("/delete", name="delete", methods={"DELETE"})
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function delete(Request $request) {
        $postId = $request->get("postId");

        if(!$postId) return new Response("Invalid request", 400);

        $em = $this->getDoctrine()->getManager();

        $post = $em->getRepository(Post::class)->find($postId);

        if(!$post) {
            throw $this->createNotFoundException('No post found for id ' . $postId);
        }

        $em->remove($post);
        $em->flush();

        return new Response("Post successfully deleted", 200);
    }
}
