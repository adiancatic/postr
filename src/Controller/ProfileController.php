<?php

namespace App\Controller;

use App\Entity\Relationship;
use App\Repository\PostRepository;
use App\Repository\RelationshipRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/u", name="profile.")
 */
class ProfileController extends AbstractController
{
    protected $userRepository;
    protected $relationshipRepository;
    protected $security;

    protected $profileUser;
    protected $currentUser;
    protected $isFollowed;

    public function __construct(UserRepository $userRepository, RelationshipRepository $relationshipRepository, Security $security)
    {
        $this->userRepository = $userRepository;
        $this->relationshipRepository = $relationshipRepository;

        $this->currentUser = $security->getUser();

        $this->security = $security;
    }

    /**
     * @Route("/follow", name="follow", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function follow(Request $request)
    {
        if(!$this->security->getUser()) {
            throw $this->createNotFoundException("Page not found");
        }

        $id = $request->get("id");
        if(!$id) return new JsonResponse("Bad request", 400);

        $userProfile = $this->userRepository->find($id);
        if(!$userProfile) return new JsonResponse("User does not exist", 204);

        $relationship = $this->relationshipRepository->findOneBy([
            "follower" => $this->currentUser,
            "followed" => $userProfile,
        ]);

        $profileUserIsFollowed = $this->relationshipRepository->isFollowed($id);
        if($profileUserIsFollowed) {
            $actionType = "unfollow";
            $relationship
                ->setActive(false)
                ->setUpdatedAt(new \DateTime());
        } else {
            $actionType = "follow";
            if($relationship) {
                if($relationship->getActive()) return new JsonResponse("User already followed", 406);
            } else {
                $relationship = new Relationship();
                $relationship
                    ->setCreatedAt(new \DateTime());
            }

            $relationship
                ->setFollower($this->currentUser)
                ->setFollowed($userProfile)
                ->setActive(true)
                ->setUpdatedAt(new \DateTime());
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($relationship);
        $em->flush();

        $return =  [
            "actionType" => $actionType,
            "followBtn" => $this->renderView("profile/components/follow-btn.html.twig", [
                "user" => $userProfile,
                "isFollowed" => !$profileUserIsFollowed,
            ]),
            "followStats" => $this->renderView("profile/components/follow-stats.html.twig", [
                "follows" => $this->relationshipRepository->getFollowsOf($userProfile),
                "followers" => $this->relationshipRepository->getFollowersOf($userProfile),
            ]),
        ];

        return new JsonResponse(json_encode($return), 200);
    }

    /**
     * @Route("/{username}", name="index")
     * @param $username
     * @param PostRepository $postRepository
     * @return Response
     * @throws \Exception
     */
    public function index($username, PostRepository $postRepository): Response
    {
        $this->profileUser = $this->userRepository->findOneBy(["username" => $username]);
        if(!$this->profileUser) {
            throw $this->createNotFoundException("User not found");
        }

        $this->isFollowed = $this->relationshipRepository->isFollowed($this->profileUser->getId());

        $follows = $this->relationshipRepository->getFollowsOf($this->profileUser->getId());
        $followers = $this->relationshipRepository->getFollowersOf($this->profileUser->getId());

        $posts = $postRepository->findByIdWithAuthors($this->profileUser->getId());

        return $this->render("profile/index.html.twig", [
            "user" => $this->profileUser,
            "posts" => $posts,
            "follows" => $follows,
            "followers" => $followers,
            "isFollowed" => $this->isFollowed,
        ]);
    }
}
