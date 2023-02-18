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
        $calData = (object)[
            'startDate' => '2020-01-01',
            'minDate'   => '2021-09-01',
            'maxDate'   => '2021-08-31',
            'data'      => [
                (object)[
                    'date'  => '2020-01-01EST',
                    'value' => 5,
                ],
                //      {date: '2020-01-01EST', value: 5},
                //      {date: '2020-01-02EST', value: 14},
                //      {date: '2020-01-03EST', value: 22},
                //      {date: '2020-01-04EST', value: 25},
                //      {date: '2020-01-05EST', value: 0},
                //      {date: '2020-01-06EST', value: 0},
                //      {date: '2020-01-07EST', value: 0},
                //      {date: '2020-01-08EST', value: 0},
                //      {date: '2020-01-09EST', value: 0},
            ],
        ];
        
        return $this->render('test_cal_heatmap/index.html.twig', [
            'controller_name' => 'TestCalHeatmapController',
            'caldata'         => json_encode($calData, JSON_THROW_ON_ERROR),
        ]);
    }
    
}
