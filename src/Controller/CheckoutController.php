<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Form\CheckoutType;
use App\Repository\ProductRepository;
use App\Service\StripeService;
use App\Service\TotalPriceService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CheckoutController extends AbstractController
{
    /**
     * @throws ApiErrorException
     */
    #[Route('/checkout', name: 'app_order')]
    public function index(
        Request $request,
        TotalPriceService $totalPriceService,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
        StripeService $stripeService
    ): Response {
        $cart = $request->getSession()->get('cart', []);

        if (empty($cart)) {
            $this->addFlash('danger', ['color' => 'light', 'message' => 'Votre panier est vide.']);

            return $this->redirectToRoute('app_shop');
        }

        $order = new Order();
        $form = $this->createForm(CheckoutType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Save order
            $paymentMethod = $form->get('paymentMethod')->getData();
            $order->setPaymentMethod($paymentMethod);
            $order->setTotalPrice($totalPriceService->getTotalPrice($order->getCity()->getId()));

            // Save order product
            foreach ($cart as $id => $quantity) {
                $product = $productRepository->find($id);
                $orderProduct = new OrderProduct()
                    ->setOrder($order)
                    ->setProduct($product)
                    ->setQuantity($quantity);

                $entityManager->persist($orderProduct);
            }

            if ($paymentMethod->getType() === 'cod') {
                $order->setStatus('cod');

                $this->addFlash('success', ['color' => 'light', 'message' => 'Votre commande a été envoyé.']);

                $entityManager->persist($order);
            }

            if ($paymentMethod->getType() === 'stripe') {
                $order->setStatus('pending');

                $entityManager->persist($order);
                $entityManager->flush();

                $session = $stripeService->checkout(
                    $this->generateUrl(
                        'app_stripe_success',
                        ['id' => $order->getId()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                    $this->generateUrl(
                        'app_stripe_cancel',
                        ['id' => $order->getId()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                    $order->getId()
                );

                return $this->redirect($session->url, 303);
            }


            $request->getSession()->remove('cart');

            $entityManager->flush();

            return $this->redirectToRoute('app_shop');
        }

        return $this->render('checkout/index.html.twig', [
            'form' => $form->createView(),
            'total' => $totalPriceService->getProductTotalPrice(),
        ]);
    }

    #[Route('/paiement_stripe/success/{id}', 'app_stripe_success')]
    public function success(Order $order, EntityManagerInterface $entityManager, Request $request): Response
    {
        $order->setStatus('stripe');

        $entityManager->flush();

        $request->getSession()->remove('cart');

        return $this->render('checkout/stripe/success.html.twig');
    }

    #[Route('/paiement_stripe/cancel{id}', 'app_stripe_cancel')]
    public function cancel(Order $order, EntityManagerInterface $entityManager, Request $request): Response
    {
        $order->setStatus('canceled');

        $entityManager->flush();

        $request->getSession()->remove('cart');

        return $this->render('checkout/stripe/cancel.html.twig');
    }
}
