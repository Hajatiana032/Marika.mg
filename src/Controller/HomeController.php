<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'current_menu' => 'home',
            'latest_products' => $productRepository->findBy([], ['createdAt' => 'DESC'], 4),
            'categories' => $categoryRepository->findBy([], ['id' => 'DESC'], 6),
        ]);
    }


}
