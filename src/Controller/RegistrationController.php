<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param Security $security
     * @param UserRepository $userRepository
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function index(Request $request, UserPasswordEncoderInterface $passwordEncoder, Security $security, UserRepository $userRepository, AuthenticationUtils $authenticationUtils): Response
    {
        if($security->getUser()) {
            return $this->redirectToRoute("home");
        }

        $form = $this
            ->createFormBuilder()
            ->add("username", TextType::class, [
                "label" => "Username",
            ])
            ->add("password", RepeatedType::class, [
                "type" => PasswordType::class,
                "required" => true,
                "first_options" => ["label" => "Password"],
                "second_options" => ["label" => "Confirm password"],
            ])
            ->add("Register", SubmitType::class, [
                "attr" => [
                    "class" => "btn btn-primary"
                ]
            ])
            ->getForm();

        $form->handleRequest($request);


        $error = $authenticationUtils->getLastAuthenticationError();

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $userAlreadyExists = $userRepository->findOneBy(["username" => $data["username"]]);

            if(!$userAlreadyExists) {
                $user = new User();
                $user->setUsername($data["username"]);
                $user->setPassword(
                    $passwordEncoder->encodePassword($user, $data["password"])
                );

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                return $this->redirect($this->generateUrl("home"));
            }

            // $error =
        }

        return $this->render('security/register.html.twig', [
            "form" => $form->createView(),
            "error" => $error,
        ]);
    }
}
