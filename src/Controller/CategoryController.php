<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    public function __construct(private readonly CategoryRepository $categoryRepository)
    {
    }

    #[Route('/catégorie/{slug}', name: 'app_category')]
    public function index(#[MapEntity(mapping: ['slug' => 'slug'])] Category $category): Response
    {
        return $this->render('category/index.html.twig', [
            'current_menu' => 'category',
            'category' => $category,
            'current_category' => $category->getSlug(),
        ]);
    }

    #[Template('partials/_categories.html.twig')]
    public function categories(?string $current_category = null): array
    {
        return [
            'categories' => $this->categoryRepository->findBy([], ['name' => 'ASC']),
            'current_category' => $current_category,
        ];
    }
}
