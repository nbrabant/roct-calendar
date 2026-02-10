<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\PlayerCategory;
use App\Entity\Season;
use App\Repository\EventRepository;
use App\Repository\PlayerCategoryRepository;
use App\Repository\SeasonRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private PlayerCategoryRepository $playerCategoryRepository,
        private SeasonRepository $seasonRepository,
        private EventRepository $eventRepository,
        private AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'playerCategoryCount' => $this->playerCategoryRepository->count(),
            'playerCategoryUrl' => $this->adminUrlGenerator
                ->setController(PlayerCategoryCrudController::class)
                ->setAction('index')
                ->generateUrl(),
            'seasonCount' => $this->seasonRepository->count(),
            'seasonUrl' => $this->adminUrlGenerator
                ->setController(SeasonCrudController::class)
                ->setAction('index')
                ->generateUrl(),
            'eventCount' => $this->eventRepository->count(),
            'eventUrl' => $this->adminUrlGenerator
                ->setController(EventCrudController::class)
                ->setAction('index')
                ->generateUrl(),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Roct Calendar')
            ->setFaviconPath('favicon.ico')
            ->setLocales(['fr']);
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            ->setDateFormat('dd/MM/yyyy')
            ->setDateTimeFormat('dd/MM/yyyy HH:mm');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        yield MenuItem::section();
        yield MenuItem::linkToCrud('Événements', 'fa fa-futbol', Event::class);
        yield MenuItem::section();
        yield MenuItem::linkToCrud('Catégories de joueurs', 'fa fa-tags', PlayerCategory::class);
        yield MenuItem::linkToCrud('Saisons', 'fa fa-calendar', Season::class);
        yield MenuItem::section();
        yield MenuItem::linkToLogout('Déconnexion', 'fa fa-sign-out');
    }
}
