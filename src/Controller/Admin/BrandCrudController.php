<?php

namespace App\Controller\Admin;

use App\Entity\Brand;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminRoute('marques')]
class BrandCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Brand::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setPageTitle(Crud::PAGE_INDEX, '<h1>Liste des marques</h1>')
            ->setPaginatorPageSize(15)
            ->setPageTitle(Crud::PAGE_NEW, '<h1>Nouvelle marque</h1>')
            ->setPageTitle(
                Crud::PAGE_EDIT,
                fn(
                    Brand $brand
                ) => "<h1>Modification de la marque <span class='text-warning'>{$brand->getName()}</span></h1>"
            );
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $action) => $action->setIcon('fa fa-plus')->setLabel('Nouvelle marque'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom'),
            SlugField::new('slug', 'Slug')->setTargetFieldName('name')->onlyOnForms(),
        ];
    }
}
