<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\Persistence\ManagerRegistry;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdministrationController extends AbstractController
{
    #[Route('/administration', name: 'app_administration')]
    public function index(): Response
    {
        return $this->render('administration/index.html.twig', [
            'controller_name' => 'AdministrationController',
        ]);
    }

    #[Route('/administration/users', name: 'administration_users')]
    public function listUsers(ManagerRegistry $doctrine): Response
    {
        $users = $doctrine->getRepository(User::class)->findAll();

        return $this->render('administration/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('administration/delete_user/{userId}', name: 'administration_deleteUser')]
    public function deleteUser(int $userId): Response
    {
        return new Response(content: $userId, status: 501);
    }

    #[Route('administration/edit_user/{userId}', name: 'administration_editUser')]
    public function editUser(int $userId): Response
    {
        return new Response(content: $userId, status: 501);
    }

    #[Route('administration/new_user', name: 'administration_newUser')]
    public function newUser(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {
        $user = new User();

        $form = $this->createFormBuilder($user)
            ->add('username', TextType::class, [
                'label' => 'Benutzname',
                'required' => true,
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Vorname',
                'required' => true,
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nachname',
                'required' => true,
                ])
            ->add('instructor', CheckboxType::class, [
                'label' => 'Ausbilder',
                'required' => true,
                ])
            ->add('password', TextType::class, [
                'label' => 'Passwort',
                'required' => true,
            ])
            ->add('password_repeat', TextType::class, [
                'label' => 'Passwort wiederholen',
                'required' => true,
                'mapped' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Benutzer erstellen'
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $passwordRepeat = $request->get('form')['password_repeat'];
            if ($user->getPassword() !== $passwordRepeat) {
                throw new RuntimeException("Passwörter müssen übereinstimmen: ".$user->getPassword()." != $passwordRepeat");
            }
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
            
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('administration_users');
        }

        $formView = $form->createView();
        return $this->render('administration/newUser.html.twig', [
            'form' => $formView,
        ]);
    }
}
