<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    
    #[Route('/registration', name: 'app_registration')]
    public function createUser(    
            Request $request,
            UserPasswordHasherInterface $passwordHasher,
            EntityManagerInterface $em
        ): Response {
            $user = new User();
            
            $form = $this->createFormBuilder($user)
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
                    'mapped'   => false,
                ])
                ->add('save', SubmitType::class, [
                    'label' => 'Benutzer erstellen',
                ])
                ->getForm();
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                $user           = $form->getData();
                $user->setRoles(["ROLE_USER"]);
                $passwordRepeat = $request->get('form')['password_repeat'];
                
                if ($user->getPassword() !== $passwordRepeat) {
                    $this->addFlash('error', 'Passwörter müssen übereinstimmen');
                    
                    $formView = $form->createView();
                    return $this->render('administration/newUser.html.twig', [
                        'form' => $formView,
                    ]);
                }
                
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $user->getPassword()
                );
                $user->setPassword($hashedPassword);
                
                $em->persist($user);
                $em->flush();
                
                $this->addFlash('success', 'Konto erfolgerich angelegt');
                
                return $this->redirectToRoute('administration_users');
            }
            
            $formView = $form->createView();
            return $this->render('registration-login/registration.html.twig', [
                'form' => $formView,
            ]);
    }
    
}
