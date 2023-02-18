<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    
    #[Route('/registration', name: 'app_registration')]
    public function renderRegistration(): Response
    {
        return $this->render('registration-login/registration.html.twig');
    }
    
    #[Route('/registration/azubi', name: 'app_registration_azubi')]
    public function renderRegistrationAzubi(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        ManagerRegistry $doctrine,
        EntityManagerInterface $em,
    ): Response {
        $form = $this->createFormBuilder()
            ->add('username', TextType::class, [
                'label'    => 'Benutzername',
                'required' => true,
            ])
            ->add('firstname', TextType::class, [
                'label'    => 'Vorname',
                'required' => true,
            ])
            ->add('lastname', TextType::class, [
                'label'    => 'Nachname',
                'required' => true,
            ])
            ->add('apprenticeship_token', TextType::class, [
                'label'    => 'Ausbildungstoken',
                'required' => true,
            ])
            ->add('password', PasswordType::class, [
                'label'    => 'Passwort',
                'required' => true,
            ])
            ->add('password_repeat', PasswordType::class, [
                'label'    => 'Passwort wiederholen',
                'required' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Azubi-Account erstellen',
            ])
            ->getForm();
        
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            // TODO: Sobald Apprenticeship gemerged wurde hier weitermachen!
            $this->addFlash('success', 'Erfolgreich registriert! Sie können sich nun anmelden.');
            return $this->redirectToRoute('app_login');
        }
        
        $formView = $form->createView();
        return $this->render('registration-login/registrationAzubi.html.twig', [
            'form' => $formView,
        ]);
    }
    
    #[Route('/registration/ausbilder', name: 'app_registration_ausbilder')]
    public function renderRegistrationAusbilder(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        ManagerRegistry $doctrine,
        EntityManagerInterface $em,
    ): Response {
        $form = $this->createFormBuilder()
            ->add('username', TextType::class, [
                'label'    => 'Benutzername',
                'required' => true,
            ])
            ->add('firstname', TextType::class, [
                'label'    => 'Vorname',
                'required' => true,
            ])
            ->add('lastname', TextType::class, [
                'label'    => 'Nachname',
                'required' => true,
            ])
            ->add('password', PasswordType::class, [
                'label'    => 'Passwort',
                'required' => true,
            ])
            ->add('password_repeat', PasswordType::class, [
                'label'    => 'Passwort wiederholen',
                'required' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Ausbilder-Account erstellen',
            ])
            ->getForm();
        
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $user = new User();
            $user->setUsername($request->get('form')['username']);
            $user->setFirstname($request->get('form')['firstname']);
            $user->setLastname($request->get('form')['lastname']);
            $user->setPassword($request->get('form')['password']);
            
            if ($user->getPassword() !== $request->get('form')['password_repeat']) {
                $this->addFlash('error', 'Passwörter stimmen nicht überein!');
                
                $formView = $form->createView();
                return $this->render('registration-login/registrationAusbilder.html.twig', [
                    'form' => $formView,
                ]);
            }
            
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);
            
            $user->setRoles(['ROLE_USER', 'ROLE_AUSBILDER']);
            
            $em->persist($user);
            $em->flush();
            
            $this->addFlash('success', 'Erfolgreich registriert! Sie können sich nun anmelden.');
            return $this->redirectToRoute('app_login');
        }
        
        $formView = $form->createView();
        return $this->render('registration-login/registrationAusbilder.html.twig', [
            'form' => $formView,
        ]);
    }
    
}
