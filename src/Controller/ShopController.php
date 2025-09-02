<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\FilterFormType;
use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
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
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        $products = $paginator->paginate(
            $this->productRepository->findBy([], ['createdAt' => 'DESC']),
            $request->query->getInt('page', 1),
            21
        );

        $form = $this->createForm(FilterFormType::class);

        if ( ! empty($request->query->all())) {
            $products = $paginator->paginate(
                $this->productRepository->searchProduct($request->query->all()),
                $request->query->getInt('page', 1),
                21
            );
        }

        return $this->render('shop/index.html.twig', [
            'current_menu' => 'shop',
            'products' => $products,
            'form' => $form,
        ]);
    }

    #[Route('/boutique/{slug}', name: 'app_shop_show')]
    public function show(Product $product): Response
    {
        return $this->render('shop/show.html.twig', ['current_menu' => 'shop', 'product' => $product]);
    }
}
