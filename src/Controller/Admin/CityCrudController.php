<?php

namespace App\Controller\Admin;

use App\Entity\City;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminRoute('villes')]
class CityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return City::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des villes')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouvelle ville')
            ->setPageTitle(
                Crud::PAGE_EDIT,
                fn($entity) => 'Modification "<span class="text-warning">'.$entity->getName().'</span>"'
            );
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $action) => $action->setLabel('Nouvelle ville')
                ->setIcon('fa fa-add'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Ville'),
            MoneyField::new('shippingCost', 'Frais de livraison')->setCurrency('MGA')->setStoredAsCents(false)
                ->setNumDecimals(0),
        ];
    }
}
