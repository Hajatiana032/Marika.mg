<?php

namespace App\Twig\Components;

use App\Entity\Order;
use App\Form\CheckoutType;
use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class CheckoutForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use ComponentToolsTrait;

    #[LiveProp]
    public ?Order $initialFormData = null;

    #[LiveProp(writable: true)]
    public ?int $cityId = null;


    public function __construct(private readonly CityRepository $cityRepository)
    {
    }

    #[LiveAction]
    public function shippingCost(): void
    {
        $this->emit('updateIdValue', ['cityId' => $this->cityId]);
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(CheckoutType::class, $this->initialFormData);
    }
}
