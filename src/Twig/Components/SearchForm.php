<?php

namespace App\Twig\Components;

use App\Form\SearchFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class SearchForm extends AbstractController
{
    use ComponentWithFormTrait;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(SearchFormType::class);
    }
}
