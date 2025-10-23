<?php

namespace App\Twig\Components;

use App\Repository\CityRepository;
use App\Service\TotalPriceService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class TotalPrice
{
    use DefaultActionTrait;

    #[LiveProp]
    public int $value = 0;

    #[LiveProp(writable: true)]
    public float $price = 0;

    public function __construct(
        private readonly TotalPriceService $totalPriceService,
        private readonly CityRepository $cityRepository
    ) {
    }

    #[LiveListener('updateIdValue')]
    public function totalPrice(#[LiveArg] int $cityId): float
    {
        $city = $this->cityRepository->find($cityId);
        $this->price = $this->totalPriceService->getTotalPrice($city->getId());

        return $this->price;
    }
}
