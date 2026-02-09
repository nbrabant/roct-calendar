<?php

namespace App\Controller\Admin;

use App\Entity\PlayerCategory;
use App\Entity\Season;
use App\Repository\PlayerCategoryRepository;
use App\Repository\SeasonRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
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
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Roct Calendar');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Player Categories', 'fa fa-tags', PlayerCategory::class);
        yield MenuItem::linkToCrud('Seasons', 'fa fa-calendar', Season::class);
    }
}
