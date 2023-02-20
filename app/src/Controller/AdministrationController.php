<?php

namespace App\Controller;

use App\Entity\Apprenticeship;
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
use Symfony\Component\Security\Core\User\UserInterface;

class AdministrationController extends AbstractController
{
    
    #[Route('/administration', name: 'app_administration')]
    public function index(): Response
    {
        return $this->render('administration/index.html.twig', [
            'controller_name' => 'AdministrationController',
        ]);
    }
    
    #[Route('/administration/apprenticeships', name: 'administration_apprenticeships')]
    public function listApprenticeships(ManagerRegistry $doctrine): Response
    {
        $apprenticeships = $doctrine->getRepository(Apprenticeship::class)->findAll();
        
        $list = [];
        
        foreach ($apprenticeships as $apprenticeship) {
            $list[] = (object)[
                'apprenticeship' => $apprenticeship,
                'azubi'          => $doctrine->getRepository(User::class)->find($apprenticeship->getAzubiId())
                        ?->getUsername() ?? '--',
                'ausbilder'      => $doctrine->getRepository(User::class)->find($apprenticeship->getAusbilderId())
                        ?->getUsername() ?? '--',
            ];
        }
        
        return $this->render('administration/apprenticeships.html.twig', [
            'apprenticeships' => $list,
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
    public function deleteUser(
        Request $request,
        ManagerRegistry $doctrine,
        EntityManagerInterface $em,
        int $userId
    ): Response {
        $user = $doctrine->getRepository(User::class)->findOneBy(
            ['id' => $userId]
        );
        
        if ($user === null) {
            return new Response(content: 'User not found', status: 404);
        }
        
        $form = $this->createFormBuilder()
            ->add('delete', CheckboxType::class, [
                'label'    => 'Ja',
                'required' => true,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Benutzer löschen',])
            ->getForm();
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->get('form')['delete']) {
                $em->remove($user);
                $em->flush();
                $this->addFlash(
                    'success',
                    'Benutzer wurde erfolgreich gelöscht'
                );
            } else {
                $this->addFlash('info', 'Benutzer wurde nicht gelöscht');
            }
            return $this->redirectToRoute('administration_users');
        }
        
        $formView = $form->createView();
        return $this->render('administration/deleteUser.html.twig', [
            'form' => $formView,
        ]);
    }
    
    #[Route('administration/edit_user/{userId}', name: 'administration_editUser')]
    public function editUser(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        ManagerRegistry $doctrine,
        EntityManagerInterface $em,
        UserInterface $userTot,
        int $userId
    ): Response {
        $user = $doctrine->getRepository(User::class)->findOneBy(
            ['id' => $userId]
        );
        
        if ($user === null) {
            throw new HttpException(404, 'User not found');
        }
        
        $user->eraseCredentials();
        
        $form = $this->genForm($user, AdministrationUserSubmitType::EDIT);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $newUser        = $form->getData();
            $passwordRepeat = $request->get('form')['password_repeat'];
            
            if (!empty($newUser->getPassword())
                && $newUser->getPassword() === $passwordRepeat
            ) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $newUser,
                    $newUser->getPassword()
                );
                $newUser->setPassword($hashedPassword);
            } elseif (!empty($newUser->getPassword())) {
                $this->addFlash('error', 'Passwörter müssen übereinstimmen');
                
                $formView = $form->createView();
                return $this->render('administration/changeUser.html.twig', [
                    'form' => $formView,
                ]);
            } else {
                $newUser->setPassword($userTot->getPassword());
            }
            $em->persist($newUser);
            $em->flush();
            
            $this->addFlash('success', 'Benutzer erfolgreich bearbeitet');
            
            return $this->redirectToRoute('administration_users');
        }
        
        $formView = $form->createView();
        return $this->render('administration/changeUser.html.twig', [
            'form' => $formView,
        ]);
    }
    
    private function genForm(User $user, AdministrationUserSubmitType $type): FormInterface
    {
        return $this->createFormBuilder($user)
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
            ->add('roles', ChoiceType::class, [
                'label'    => 'admin',
                'required' => true,
                'multiple' => true,
                'expanded' => true,
                'choices'  => [
                    'User'      => 'ROLE_USER',
                    'Admin'     => 'ROLE_ADMIN',
                    'Azubi'     => 'ROLE_AZUBI',
                    'Ausbilder' => 'ROLE_AUSBILDER',
                ],
            ])
            ->add('password', PasswordType::class, [
                'label'    => 'Passwort',
                'required' => $type === AdministrationUserSubmitType::CREATE,
            ])
            ->add('password_repeat', PasswordType::class, [
                'label'    => 'Passwort wiederholen',
                'required' => $type === AdministrationUserSubmitType::CREATE,
                'mapped'   => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => match ($type) {
                    AdministrationUserSubmitType::CREATE => 'Benutzer erstellten',
                    AdministrationUserSubmitType::EDIT => 'Benutzer bearbeiten',
                },
            ])
            ->getForm();
    }
    
    #[Route('administration/new_user', name: 'administration_newUser')]
    public function newUser(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response {
        $user = new User();
        
        $form = $this->genForm($user, AdministrationUserSubmitType::CREATE);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $user           = $form->getData();
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
            
            $this->addFlash('success', 'Benutzer erfolgreich erstellt');
            
            return $this->redirectToRoute('administration_users');
        }
        
        $formView = $form->createView();
        return $this->render('administration/newUser.html.twig', [
            'form' => $formView,
        ]);
    }
    
}

enum AdministrationUserSubmitType
{
    
    case CREATE;
    case EDIT;
    
}
