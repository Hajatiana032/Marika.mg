<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class StripeService
{
    public function __construct(
        private RequestStack $request,
        private string $stripeSK,
        private ProductRepository $productRepository,
    ) {
    }

    /**
     * @throws ApiErrorException
     */
    public function checkout(string $urlSuccess, string $urlCancel, string $sessionId): Session
    {
        $cart = $this->request->getSession()->get('cart', []);

        $stripe = new StripeClient($this->stripeSK);

        $lineItems = [];
        foreach ($cart as $id => $quantity) {
            $product = $this->productRepository->find($id);

            if ( ! $product) {
                continue;
            }

            $image = $product->getImages()->last();
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'mga',
                    'product_data' => [
                        'name' => $product->getTitle(),
                    ],
                    'unit_amount' => $product->getPrice(),
                ],
                'quantity' => $quantity,
            ];
        }

        return $stripe->checkout->sessions->create([
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $urlSuccess,
            'cancel_url' => $urlCancel,
            'shipping_address_collection' => ['allowed_countries' => ['MG']],
        ]);
    }

}
