<?php

namespace App\Controller\Admin;

use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\City;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Testimonial;
use App\Entity\User;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\CityRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\TestimonialRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ChartBuilderInterface $chartBuilder,
        private readonly CategoryRepository $categoryRepository,
        private readonly BrandRepository $brandRepository,
        private readonly ProductRepository $productRepository,
        private readonly OrderRepository $orderRepository,
        private readonly CityRepository $cityRepository,
        private readonly TestimonialRepository $testimonialRepository
    ) {
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'users' => $this->userRepository->findAll(),
            'categories' => $this->categoryRepository->findAll(),
            'brands' => $this->brandRepository->findAll(),
            'products' => $this->productRepository->findAll(),
            'orders' => $this->orderRepository->findAll(),
            'cities' => $this->cityRepository->findAll(),
            'testimonials' => $this->testimonialRepository->findAll(),
            'chartUser' => $this->chartUser(),
            'chartOrders' => $this->chartOrders(),
        ]);
    }

    public function chartUser(): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $users = $this->userRepository->findAll();
        $admins = 0;
        $simpleUsers = 0;
        $data = [];
        foreach ($users as $user) {
            $roles = $user->getRoles();
            if (in_array('ROLE_ADMIN', $roles)) {
                $admins++;
            } else {
                $simpleUsers++;
            }
        }
        $chart->setData([
            'labels' => [
                'Simple utilisateurs',
                'Administrateur',
            ],
            'datasets' => [
                [
                    'label' => 'Nombre d\'utilisateurs',
                    'data' => [$simpleUsers, $admins],
                    'backgroundColor' => ['rgba(13,110,253,1)', 'rgba(255,193,7,1)'],
                ],
            ],
        ]);

        return $chart;
    }

    public function chartOrders(): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $orders = $this->orderRepository->findAll();
        $cod = 0;
        $stripe = 0;
        $pending = 0;
        $canceled = 0;
        $data = [];
        foreach ($orders as $order) {
            if ($order->getStatus() === 'cod') {
                $cod++;
            } elseif ($order->getStatus() === 'stripe') {
                $stripe++;
            } elseif ($order->getStatus() === 'pending') {
                $pending++;
            } else {
                $canceled++;
            }
        }
        $chart->setData([
            'labels' => [
                'A la livraison',
                'Par carte(Stripe)',
                'En attente',
                'Annuler',
            ],
            'datasets' => [
                [
                    'label' => 'Paiement',
                    'data' => [$cod, $stripe, $pending, $canceled],
                    'backgroundColor' => [
                        'rgba(13,110,253,1)',
                        'rgba(25,135,84,1)',
                        'rgba(255,193,7,1)',
                        'rgba(220,38,38,1)',
                    ],
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
        yield MenuItem::linkToCrud('Villes', 'fas fa-location', City::class);
        yield MenuItem::linkToCrud('Produits', 'fa fa-boxes', Product::class);
        yield MenuItem::linkToCrud('Commandes', 'fa fa-receipt', Order::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class);
        yield MenuItem::linkToCrud('Témoignages', 'fa fa-comments', Testimonial::class);
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addAssetMapperEntry('admin');
    }

    public function configureActions(): Actions
    {
        return parent::configureActions()
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->update(
                Crud::PAGE_INDEX,
                Action::EDIT,
                fn(Action $action) => $action->setIcon('fa fa-edit')->setCssClass('text-warning fw-semibold')
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::DELETE,
                fn(Action $action) => $action->setIcon('fa fa-trash')->setCssClass('text-danger fw-semibold')
            )
            ->update(
                Crud::PAGE_NEW,
                Action::SAVE_AND_RETURN,
                fn(Action $action) => $action->setIcon('fa fa-save')->setLabel('Enregistrer')
            )
            ->update(
                Crud::PAGE_EDIT,
                Action::SAVE_AND_RETURN,
                fn(Action $action) => $action->setIcon('fa fa-save')->addCssClass('btn btn-warning')
            );
    }

    /**
     * @throws \Exception
     */
    public function configureUserMenu(UserInterface $user): UserMenu
    {
        if ( ! $user instanceof User) {
            throw new \Exception('Wrong user');
        }

        $avatarUrl = 'img/uploads/avatar/'.$user->getAvatar();

        return parent::configureUserMenu($user)
            ->setAvatarUrl($avatarUrl);
    }
}
