<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\ChildRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{


    /**
     * @Route("/register", name="app_register")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param Swift_Mailer $mailer
     * @param UserRepository $userRepository
     * @param ChildRepository $childRepository
     * @return Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder,
                             Swift_Mailer $mailer, UserRepository $userRepository,
                             ChildRepository $childRepository): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setActivationToken(md5(uniqid()));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            $message = (new \Swift_Message('Nouveau compte'))
                // On attribue l'expéditeur
                ->setFrom(['crechelacalinerie@gmail.com' => 'LA CALINERIE '])
                // On attribue le destinataire
                ->setTo($user->getEmail())
                // On crée le texte avec la vue
                ->setBody(
                    $this->renderView(
                        'emails/activation.html.twig', ['token' => $user->getActivationToken()]
                    ),
                    'text/html'
                )
            ;

            $mailer->send($message);

            return $this->render('emails/confirmation.html.twig');

        }


        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),

        ]);
    }

    /**
     * @Route("/activation/{token}", name="activation")
     * @param $token
     * @param UserRepository $userRepository
     * @return RedirectResponse
     */
    public function activation($token, UserRepository $userRepository)
    {
        // On recherche si un utilisateur avec ce token existe dans la base de données
        $user = $userRepository->findOneBy(['activationToken' => $token]);

        // Si aucun utilisateur n'est associé à ce token
        if(!$user){
            // On renvoie une erreur 404
            throw $this->createNotFoundException('Cet utilisateur n\'existe pas');
        }

        // On supprime le token
        $user->setActivationToken(null);
        $entityManager = $this->getDoctrine()->getManager();
        $user->setRoles(["ROLE_PARENT"]);
        $entityManager->persist($user);
        $entityManager->flush();

        // On génère un message
        $this->addFlash('message', 'Votre compte a été activé avec succès !');

        // On retourne à l'accueil
        return $this->redirectToRoute('home');
    }

}