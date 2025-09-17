<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\FilterFormType;
use App\Model\SearchData;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ShopController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository,
    ) {
    }

    #[Route('/boutique', name: 'app_shop')]
    public function index(Request $request): Response
    {
        $data = new SearchData();
        $form = $this->createForm(FilterFormType::class, $data);
        $form->handleRequest($request);

        $products = $this->productRepository->searchProduct($data);

        return $this->render('shop/index.html.twig', [
            'current_menu' => 'shop',
            'products' => $products,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/boutique/{slug}', name: 'app_shop_show')]
    public function show(Product $product): Response
    {
        return $this->render('shop/show.html.twig', ['current_menu' => 'shop', 'product' => $product]);
    }
}
