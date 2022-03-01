<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\Persistence\ManagerRegistry;


use App\Entity\User;
use App\Form\RegisterType;


class UserController extends AbstractController
{
    
    public function register(Request $request,  UserPasswordHasherInterface $passwordHasher, ManagerRegistry $doctrine): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $user->setRole('ROLE_USER');
          
            $user->setCreatedAt(new \DateTime('now'));
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
            
            $em = $doctrine->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('login');
        }

        return $this->render('user/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function login(AuthenticationUtils $authenticationUtils){
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername =$authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig',[
            'error' => $error,
            'last_username' =>$lastUsername
        ]);
    }
}
