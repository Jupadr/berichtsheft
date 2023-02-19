<?php

namespace App\Controller;

use App\Entity\Apprenticeship;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


class DashboardController extends AbstractController
{
    #[Route('/dashboard/addAzubi', name: 'add_azubi')]
    public function addAzubi($apprenticeship): FormInterface
    {
        return $this->createFormBuilder()
        ->add('azubiId', IntegerType::class, [
            'label' => 'Azubi Id',
            'required' => true,
        ])
        ->add('startYear', DateTimeType::class, [
              'label' => 'Beginn Ausbildung',
              'required' => true,
        ])
        ->add('endYear', DateTimeType::class, [
              'label' => 'Ende Ausbildung',
              'required' => true,
              ])
        ->add('save', SubmitType::class, [
            'label' => 'Einladungslink erstellen',
        ])
        ->getForm();            
    }

    #[Route('dashboard/createApprenticeship', name: 'create_apprenticeship')]
    public function saveApprenticeship(
        Request $request,
        EntityManagerInterface $em,
    ): Response{
        $uuid = Uuid::v4();
        $apprenticeship = new Apprenticeship;
        $apprenticeship->setInviteToken($uuid->toRfc4122());
        $user = $this->getUser();
        if (in_array('ROLE_AUSBILDER', $user->getRoles(), true)) {
            $apprenticeship->setInstructorId($user);
        }
        $form = $this->addAzubi($apprenticeship);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            var_dump($form->getData());
            $apprenticeship = $form->getData();
            $em->persist($apprenticeship);
            $em->flush();
            $this->addFlash('success', 'Ausbildung angelegt');
        }
        $formView = $form->createView();
        return $this->render('dashboard/dashboardAddAzubi.html.twig', [
            'form' => $formView,
        ]);
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        $user = $this->getUser();


        
        if (in_array('ROLE_AZUBI', $user->getRoles(), true)) {
            return $this->render('dashboard/dashboardAzubi.html.twig');
        }
        
        if (in_array('ROLE_AUSBILDER', $user->getRoles(), true)) {
            return $this->render('dashboard/dashboardAusbilder.html.twig');
        }
        
        return $this->redirectToRoute('app_login');
    }
    
}
