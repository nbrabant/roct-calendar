<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\PlayerCategoryRepository;
use App\Repository\SeasonRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
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
        $filterData = $this->getFilteredEvents($request, $seasonRepository, $eventRepository, $playerCategoryRepository);

        return $this->render('home/index.html.twig', $filterData);
    }

    #[Route('/export-pdf', name: 'app_export_pdf')]
    public function exportPdf(
        Request $request,
        SeasonRepository $seasonRepository,
        EventRepository $eventRepository,
        PlayerCategoryRepository $playerCategoryRepository,
    ): Response {
        $filterData = $this->getFilteredEvents($request, $seasonRepository, $eventRepository, $playerCategoryRepository);

        $html = $this->renderView('home/export_pdf.html.twig', $filterData);

        $options = new Options();
        $options->setDefaultFont('Helvetica');
        $options->setIsRemoteEnabled(false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="calendrier-roct.pdf"',
        ]);
    }

    /**
     * @return array{season: ?\App\Entity\Season, events: array, categories: array, selectedCategories: array, dateFrom: ?string, dateTo: ?string}
     */
    private function getFilteredEvents(
        Request $request,
        SeasonRepository $seasonRepository,
        EventRepository $eventRepository,
        PlayerCategoryRepository $playerCategoryRepository,
    ): array {
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

        return [
            'season' => $season,
            'events' => $events,
            'categories' => $categories,
            'selectedCategories' => $selectedCategories,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ];
    }
}
