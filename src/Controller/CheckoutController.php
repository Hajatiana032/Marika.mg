<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Form\CheckoutType;
use App\Repository\ProductRepository;
use App\Service\TotalPriceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CheckoutController extends AbstractController
{


    #[Route('/checkout', name: 'app_order')]
    public function index(
        Request $request,
        TotalPriceService $totalPriceService,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
    ): Response {
        $cart = $request->getSession()->get('cart', []);
        $order = new Order();
        $form = $this->createForm(CheckoutType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Save order
            $order->setPayOnDelivery(true)->setTotalPrice(
                $totalPriceService->getTotalPrice($order->getCity()->getId())
            );
            $entityManager->persist($order);

            // Save order product
            foreach ($cart as $id => $quantity) {
                $product = $productRepository->find($id);
                $orderProduct = new OrderProduct()
                    ->setOrder($order)
                    ->setProduct($product)
                    ->setQuantity($quantity);
                $entityManager->persist($orderProduct);
            }
            $this->addFlash('success', ['color' => 'light', 'message' => 'Votre commande a été envoyées.']);
            $request->getSession()->remove('cart');

            $entityManager->flush();

            return $this->redirectToRoute('app_shop');
        }

        return $this->render('checkout/index.html.twig', [
            'form' => $form->createView(),
            'total' => $totalPriceService->getProductTotalPrice(),
        ]);
    }

    #[Route('/payer-via-stripe', 'app_stripe')]
    public function stripe()
    {
    }
}
