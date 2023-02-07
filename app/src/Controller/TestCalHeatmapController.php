<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestCalHeatmapController extends AbstractController
{
    #[Route('/test/cal/heatmap', name: 'app_test_cal_heatmap')]
    public function index(): Response
    {
        return $this->render('test_cal_heatmap/index.html.twig', [
            'controller_name' => 'TestCalHeatmapController',
        ]);
    }
}
