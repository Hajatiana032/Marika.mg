<?php

namespace App\Twig\Components;

use App\Repository\CityRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ShippingCost
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?int $shippingPrice = null;

    public function __construct(private readonly CityRepository $cityRepository)
    {
    }

    #[LiveListener('updateIdValue')]
    public function shipping(#[LiveArg] int $cityId): float
    {
        $city = $this->cityRepository->find($cityId);

        return $this->shippingPrice = $city->getShippingCost();
    }
}
