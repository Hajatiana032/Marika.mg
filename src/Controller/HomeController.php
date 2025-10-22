<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\TestimonialRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        TestimonialRepository $testimonialRepository
    ): Response {
        return $this->render('home/index.html.twig', [
            'current_menu' => 'home',
            'latest_products' => $productRepository->latest(),
            'categories' => $categoryRepository->someCategories(),
            'testimonials' => $testimonialRepository->latest(),
        ]);
    }


}
