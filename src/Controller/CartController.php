<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    public function __construct(private readonly ProductRepository $productRepository)
    {
    }

    #[Route('/mon_panier', name: 'app_cart')]
    public function index(Request $request): Response
    {
        $cart = $request->getSession()->get('cart', []);
        $data = [];
        $total = 0;

        foreach ($cart as $id => $quantity) {
            $product = $this->productRepository->find($id);
            $data[] = [
                'product' => $product,
                'quantity' => $quantity,
                'total' => $product->getPrice() * $quantity,
            ];
            $total = $total + $product->getPrice() * $quantity;
        }

        return $this->render('cart/index.html.twig', [
            'current_menu' => 'cart',
            'items' => $data,
            'total' => $total,
        ]);
    }

    #[Route('/cart/add/{id}', 'app_cart_add')]
    public function add(Product $product, Request $request): Response
    {
        $cart = $request->getSession()->get('cart', []);
        $id = $product->getId();

        if (empty($cart[$id])) {
            $cart[$id] = 1;
            $request->getSession()->set('cart', $cart);
            $this->addFlash('success', [
                    'color' => 'light',
                    'message' => "<strong>{$product->getTitle()}</strong> a été ajouté dans votre panier.",
                ]
            );

            return $this->redirect($request->headers->get('referer'));
        } else {
            $cart[$id]++;
            $this->update($request, $cart, $product);
        }

        return $this->redirectToRoute('app_cart');
    }

    public function update($request, array $cart, Product $product): void
    {
        $this->addFlash(
            'warning',
            [
                'color' => 'dark',
                'message' => 'La quantité <strong>'.$product->getTitle().'</strong> de votre panier a été mis à jour.',
            ]
        );
        $request->getSession()->set('cart', $cart);
    }

    #[Route('/cart/subtract/{id}', 'app_cart_subtract')]
    public function subtract(Product $product, Request $request): Response
    {
        $cart = $request->getSession()->get('cart', []);
        $id = $product->getId();

        if (empty($cart[$id])) {
            $cart[$id] = 1;
        } else {
            if ($cart[$id] > 1) {
                $cart[$id]--;
                $this->update($request, $cart);
            } else {
                unset($cart[$id]);
                $this->addFlash(
                    'danger',
                    [
                        'color' => 'white',
                        'message' => '<strong>'.$product->getTitle().'</strong> a été retiré de votre panier.',
                    ]
                );
            }
        }

        $request->getSession()->set('cart', $cart);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', 'app_cart_delete')]
    public function delete(Product $product, Request $request): Response
    {
        $cart = $request->getSession()->get('cart', []);
        $id = $product->getId();
        if ($cart[$id]) {
            unset($cart[$id]);
        }

        $request->getSession()->set('cart', $cart);

        $this->addFlash('danger', [
            'color' => 'white',
            'message' => '<strong>'.$product->getTitle().'</strong> a été retiré de votre panier.',
        ]);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove', 'app_cart_remove')]
    public function remove(Request $request): Response
    {
        $request->getSession()->remove('cart');

        return $this->redirectToRoute('app_cart');
    }
}
