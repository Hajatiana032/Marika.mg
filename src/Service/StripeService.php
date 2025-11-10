<?php

namespace App\Service;

use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\RequestStack;

class StripeService
{
    public function __construct(private readonly RequestStack $request)
    {
    }

    /**
     * @throws ApiErrorException
     */
    public function checkout(string $stripeSK): Session
    {
        $cart = $this->request->getSession()->get('cart', []);
        Stripe::setApiKey($stripeSK);

        return Session::create([
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'T-shirt',
                        ],
                        'unit_amount' => 2000,
                    ],
                    'quantity' => 1,
                ],
            ],
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'success_url' => 'http://localhost:4242/success',
            'cancel_url' => 'http://localhost:4242/cancel',
        ]);
    }


}
