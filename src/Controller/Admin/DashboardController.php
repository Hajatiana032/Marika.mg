<?php

namespace App\Controller\Admin;

use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

final class DashboardController extends AbstractController
{
    public function __construct(
        private readonly ChartBuilderInterface $chartBuilder,
        private readonly CategoryRepository $categoryRepository,
        private readonly BrandRepository $brandRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route('/admin', name: 'app_admin_dashboard')]
    public function index(): Response
    {
        return $this->render('admin/dashboard/index.html.twig', [
            'current_menu' => 'admin_dashboard',
            'chart_category' => $this->chartCategory(),
            'chart_brand' => $this->chartBrand(),
            'chart_user' => $this->chartUser(),
        ]);
    }

    public function chartCategory(): Chart
    {
        $categories = $this->categoryRepository->findAll();
        $labels = [];
        $data = [];

        foreach ($categories as $category) {
            $labels[] = $category->getName();
            $data[] = count($category->getProducts());
        }

        return $this->chart(
            $labels,
            $data,
            'Catégories',
            'Nombre de produits',
            'Noms des catégories',
            '#1C64F2',
            Chart::TYPE_LINE
        );
    }

    public function chart(
        array $labels,
        array $data,
        $label,
        string $title_y,
        string $title_x,
        string $color,
        string $type
    ):
    Chart {
        $chart = $this->chartBuilder->createChart($type);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $label,
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'data' => $data,
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'labels' => [
                        'font' => [
                            'size' => 25,
                        ],
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => $title_y,
                        'color' => $color,
                        'font' => [
                            'size' => 18,
                        ],
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 18,
                        ],
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => $title_x,
                        'color' => $color,
                        'font' => [
                            'size' => 18,
                        ],
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 14,
                        ],
                    ],
                ],
            ],
        ]);

        return $chart;
    }

    public function chartBrand(): Chart
    {
        $brands = $this->brandRepository->findAll();
        $labels = [];
        $data = [];

        foreach ($brands as $brand) {
            $labels[] = $brand->getName();
            $data[] = count($brand->getProduct());
        }

        return $this->chart(
            $labels,
            $data,
            'Marques',
            'Nombre de produits',
            'Noms des marques',
            '#00BC7DFF',
            Chart::TYPE_LINE
        );
    }

    public function chartUser(): Chart
    {
        $data = $this->userRepository->countUsersByMonth2025();

        $labels = array_map(fn($row) => $row['month'], $data);
        $data = array_map(fn($row) => $row['count'], $data);

        return $this->chart(
            $labels,
            $data,
            'Utilisateurs',
            'Total d\'inscription',
            'Années',
            'orange',
            Chart::TYPE_RADAR
        );
    }
}
