<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\PlayerCategoryRepository;
use App\Repository\SeasonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        Request $request,
        SeasonRepository $seasonRepository,
        EventRepository $eventRepository,
        PlayerCategoryRepository $playerCategoryRepository,
    ): Response {
        $season = $seasonRepository->findCurrent();

        $categories = $playerCategoryRepository->findAll();
        $selectedCategories = $request->query->all('categories');
        $dateFrom = $request->query->get('date_from');
        $dateTo = $request->query->get('date_to');

        $events = [];
        if ($season) {
            $events = $eventRepository->findBySeasonFiltered(
                $season,
                $selectedCategories ?: null,
                $dateFrom ? new \DateTime($dateFrom) : null,
                $dateTo ? new \DateTime($dateTo) : null,
            );
        }

        return $this->render('home/index.html.twig', [
            'season' => $season,
            'events' => $events,
            'categories' => $categories,
            'selectedCategories' => $selectedCategories,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
}
