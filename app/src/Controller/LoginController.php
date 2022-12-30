<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    
    #[Route('/', name: 'index')]
    public function start()
    {
        $user = $this->getUser();
        
        if ($user !== null) {
            return $this->redirectToRoute('app_dashboard');
        }
        
        return $this->redirectToRoute('app_login');
    }
    
    #[Route('/login', name: 'app_login')]
    public function index(
        AuthenticationUtils $utils,
        UserInterface $user = null
    ): Response {
        $lastUsername = $utils->getLastUsername();
        $error        = $utils->getLastAuthenticationError();
        
        if ($user !== null) {
            $this->addFlash('success', 'Erfolgreich als ' . $user->getUserIdentifier() . ' angemeldet');
        } elseif ($error) {
            $this->addFlash('error', 'Login fehlgeschlagen');
        }
        
        return $this->render('login/index.html.twig', [
            'lastUsername' => $lastUsername,
            'error'        => $error,
        ]);
    }
    
    #[Route('/logout', name: 'app_logout')]
    public function logout()
    {
        // Hier passiert nichts
    }
    
}
