<?php

namespace App\Service;

use App\Repository\CityRepository;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class TotalPriceService
{

    public function __construct(
        private RequestStack $request,
        private CityRepository $cityRepository,
        private ProductRepository $productRepository
    ) {
    }

    public function getTotalPrice(int $cityId): int
    {
        $city = $this->cityRepository->find($cityId);

        return round($this->getProductTotalPrice()) + $city->getShippingCost();
    }

    public function getProductTotalPrice(): float|int
    {
        $cart = $this->request->getSession()->get('cart', []);
        $totalProduct = 0;

        foreach ($cart as $id => $quantity) {
            $totalProduct += $quantity * $this->productRepository->find($id)->getPrice();
        }

        return $totalProduct;
    }
}
