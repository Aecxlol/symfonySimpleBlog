<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /** 
     * @Route("/inscription", name="security_registration")
    */
    public function registration(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder)
    {
        //On crée le user mais il est vide
        $user = new User();

        //Le user vaut quelque chose grâce au form rempli
        $form = $this->createForm(RegistrationType::class, $user);

        //permet d'analyser la requête
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            //Avant de faire persister mon user on hash le mot de passe en lui donnant en param $user comme $user est une instance de la classe user et qu'elle a été renseignée
            //dans security.yaml pour utiliser la méthode de cryptage bcrypt et en deuxième param le mdp à encoder
            $hash = $encoder->encodePassword($user, $user->getPassword());

            //On reset le password en lui donnant le password hashé
            $user->setPassword($hash);

            $manager->persist($user);
            $manager->flush();

            //Si les champs sont bons on redirige l'utilisateur sur le form pour se log
            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /** 
     * @Route("/login", name="security_login")
    */
    public function login(Request $request, ObjectManager $manager)
    {
        return $this->render('security/login.html.twig');
    }

    /** 
     * @Route("/logout", name="security_logout")
    */
    public function logout(){}
}
