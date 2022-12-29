<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    
    #[Route('/createUser', name: 'app_createUser')]
    public function createUser()
    {
        $lastName  = $_POST["lastName"];
        $firstName = $_POST["firstName"];
        $mail      = $_POST["e-mail"];
        $birthDate = $_POST["birth-date"];
        $password  = $_POST["passowrd"];
    }
    
    #[Route('/registration', name: 'app_registration')]
    public function renderRegistration()
    {
        return $this->render('registration-login/registration.html.twig', [
            'controller_name' => 'RegistrationController',
        ]);
    }
    
}
