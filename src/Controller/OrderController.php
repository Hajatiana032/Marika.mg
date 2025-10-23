<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Form\OrderType;
use App\Repository\ProductRepository;
use App\Service\TotalPriceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    #[Route('/validation', name: 'app_order')]
    public function index(
        Request $request,
        TotalPriceService $totalPriceService,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
    ): Response {
        $cart = $request->getSession()->get('cart', []);
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($order->isPayOnDelivery()) {
                if ( ! empty($cart)) {
                    // Save order
                    $order->setTotalPrice($totalPriceService->getTotalPrice($order->getCity()->getId()));
                    $entityManager->persist($order);

                    // Save order product
                    foreach ($cart as $id => $_) {
                        $product = $productRepository->find($id);
                        $orderProduct = new OrderProduct()
                            ->setOrder($order)
                            ->setProduct($product)
                            ->setQuantity(array_sum($cart));
                        $entityManager->persist($orderProduct);
                    }
                }
                $entityManager->flush();

                $this->addFlash('success', ['color' => 'light', 'message' => 'Votre commande a été envoyées.']);
                $request->getSession()->remove('cart');

                return $this->redirectToRoute('app_shop');
            }
        }

        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'total' => $totalPriceService->getProductTotalPrice(),
        ]);
    }
}
