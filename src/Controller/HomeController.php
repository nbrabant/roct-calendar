<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\SeasonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(SeasonRepository $seasonRepository, EventRepository $eventRepository): Response
    {
        $season = $seasonRepository->findCurrent();
        $events = $season ? $eventRepository->findBySeason($season) : [];

        return $this->render('home/index.html.twig', [
            'season' => $season,
            'events' => $events,
        ]);
    }
}
