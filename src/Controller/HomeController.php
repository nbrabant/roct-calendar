<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\PlayerCategoryRepository;
use App\Repository\SeasonRepository;
use App\Entity\Event;
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

    #[Route('/calendar.ics', name: 'app_export_ical')]
    public function exportIcal(
        Request $request,
        SeasonRepository $seasonRepository,
        EventRepository $eventRepository,
        PlayerCategoryRepository $playerCategoryRepository,
    ): Response {
        $filterData = $this->getFilteredEvents($request, $seasonRepository, $eventRepository, $playerCategoryRepository);

        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//ROCT Calendar//FR\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";
        $ical .= "X-WR-CALNAME:ROCT Calendar\r\n";

        /** @var Event $event */
        foreach ($filterData['events'] as $event) {
            $uid = $event->getId() . '@roct-calendar';
            $dtstart = $event->getEventDate()->format('Ymd');
            $summary = $this->escapeIcalText($event->getName());
            $description = $event->getDescription() ? $this->escapeIcalText($event->getDescription()) : '';
            $categories = [];
            foreach ($event->getCategories() as $category) {
                $categories[] = $this->escapeIcalText((string) $category);
            }

            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= "UID:{$uid}\r\n";
            $ical .= "DTSTART;VALUE=DATE:{$dtstart}\r\n";
            $ical .= "SUMMARY:{$summary}\r\n";
            if ($description !== '') {
                $ical .= "DESCRIPTION:{$description}\r\n";
            }
            if ($categories !== []) {
                $ical .= 'CATEGORIES:' . implode(',', $categories) . "\r\n";
            }
            $ical .= "END:VEVENT\r\n";
        }

        $ical .= "END:VCALENDAR\r\n";

        return new Response($ical, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="roct-calendar.ics"',
        ]);
    }

    private function escapeIcalText(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace("\n", '\\n', $text);
        $text = str_replace(',', '\\,', $text);
        $text = str_replace(';', '\\;', $text);

        return $text;
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
