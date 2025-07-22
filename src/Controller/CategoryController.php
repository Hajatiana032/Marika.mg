<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    public function __construct(private readonly CategoryRepository $categoryRepository)
    {
    }

    #[Route('/category', name: 'app_category')]
    public function index(): Response
    {
        return $this->render('category/index.html.twig', [
            'current_menu' => 'category',
        ]);
    }

    #[Route('/_categories', name: 'app_partial_categories')]
    public function categoriesList(): Response
    {
        return $this->render('partials/_categories.html.twig', [
            'categories' => $this->categoryRepository->findBy([],
                ['name' => 'ASC']),
        ]);
    }
}
