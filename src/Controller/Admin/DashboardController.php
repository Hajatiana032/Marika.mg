<?php

namespace App\Controller\Admin;

use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Testimonial;
use App\Repository\CategoryRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use phpDocumentor\Reflection\Types\Parent_;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly ChartBuilderInterface $chartBuilder,
        private readonly CategoryRepository $categoryRepository
    ) {}

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', ['chartCategory' => $this->chartCategory()]);
        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // 1.1) If you have enabled the "pretty URLs" feature:
        // return $this->redirectToRoute('admin_user_index');
        //
        // 1.2) Same example but using the "ugly URLs" that were used in previous EasyAdmin versions:
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirectToRoute('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function chartCategory(): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $categories = $this->categoryRepository->findAll();
        $labels = [];
        $data = [];
        foreach ($categories as $category) {
            $labels[] = $category->getName();
            $data[] = count($category->getProducts());
        }
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Catégories',
                    'data' => $data,
                ],
            ],
        ]);

        return $chart;
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setFaviconPath('img/logo.svg')
            ->setTitle('<h2 class="fw-bold text-primary text-center">Marika.mg</h2>')->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToUrl('Retour au site', 'fa fa-home', $this->generateUrl('app_home'));
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-dashboard');
        yield MenuItem::linkToCrud('Catégories', 'fa fa-tags', Category::class);
        yield MenuItem::linkToCrud('Marques', 'fa fa-tags', Brand::class);
        yield MenuItem::linkToCrud('Produits', 'fa fa-boxes', Product::class);
        yield MenuItem::linkToCrud('Témoignages', 'fa fa-comments', Testimonial::class);
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addCssFile('styles/admin.css', ['rel' => 'stylesheet', 'data-priority' => 'high']);
    }

    public function configureActions(): Actions
    {
        return parent::configureActions()
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn(Action $action) => $action->setIcon('fa fa-edit')->setCssClass('text-warning fw-bold'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn(Action $action) => $action->setIcon('fa fa-trash')->setCssClass('text-danger fw-bold'))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, fn(Action $action) => $action->setIcon('fa fa-save')->setLabel('Enregistrer'))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, fn(Action $action) => $action->setIcon('fa fa-save')->addCssClass('btn btn-warning'));
    }
}
