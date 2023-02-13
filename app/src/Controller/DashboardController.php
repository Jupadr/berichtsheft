<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard/addAzubi', name: 'add_azubi')]
    public function addAzubi(
        Request $request,
    ): Response
    {
        $form = $this->createFormBuilder()
        ->add('AzubiName', TextType::class, [
            'label' => 'Azubi Name',
            'required' => true,
      ])
        ->add('inviteLink', TextType::class, [
              'label' => 'Einladungslink',
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
        $form->handleRequest($request);
            
        $formView = $form->createView();
        return $this->render('dashboard/dashboardAddAzubi.html.twig', [
            'form' => $formView,
        ]);
    }

    public function saveTraining()
    {

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
