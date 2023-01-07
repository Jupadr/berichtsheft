<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    
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
