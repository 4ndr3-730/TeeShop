<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Form\RegisterFormType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/inscription', name: 'register', methods: ['GET', 'POST'])]
    public function register(Request $request, UserRepository $repository, UserPasswordHasherInterface $passwordHasher): Response
    {
    # $this->getUser() permet de 
    if ($this->getUser()){
        $this->addFlash('warning',"Vous êtes connecté, inscription non autorisée. <a href='/logout'>Déconnexion</a>");
        return $this ->redirectToRoute('show_home');
    }

        $user = new User();

        $form = $this->createForm(RegisterFormType::class, $user)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $user->setCreatedAt(new DateTime());
            $user->setUpdatedAt(new DateTime());

            $user->setRoles(['ROLE_USER']);


            # On doit resseter manuellement la valeur du password, car par défaut il n'est pas hashé.
            # Pour cela, nous devons utiliser une méthode de hashage appelée hashPassword() :
            #   => cette méthode attend 2 arguments : $user, $plainPassword
            $user->setPassword(
                $passwordHasher->hashPassword($user, $user->getPassword())
            );

            $repository->save($user, true);

            $this->addFlash('success', "Votre inscription a été correctement enregistrée");
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/register_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
