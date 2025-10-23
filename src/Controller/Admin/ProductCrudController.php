<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ImageFormType;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminRoute('produits')]
class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setPageTitle(Crud::PAGE_INDEX, '<h1>Liste des produits</h1>')
            ->setPaginatorPageSize(15)
            ->setPageTitle(Crud::PAGE_NEW, '<h1>Nouveau produit</h1>')
            ->setPageTitle(
                Crud::PAGE_EDIT,
                fn(
                    $entity
                ) => "<h1>Modification du produit <span class='text-warning'>{$entity->getTitle()}</span></h1>"
            )
            ->setPageTitle(
                Crud::PAGE_DETAIL,
                fn($entity) => '<span class="text-primary">'.$entity->getTitle().'</span>'
            );
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                fn(Action $action) => $action->setIcon('fa fa-plus')->setLabel('Nouveau produit')
            )
            ->add(Crud::PAGE_INDEX, 'detail')
            ->update(
                Crud::PAGE_INDEX,
                'detail',
                fn($action) => $action->setIcon('fa fa-eye')->setLabel('<span class="text-success">Détails</span>')
                    ->setHtmlAttributes(['data-turbo' => 'true'])
            )
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT]);
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title', 'Titre'),
            SlugField::new('slug', 'Slug')->setTargetFieldName('title'),
            AssociationField::new('category', 'Sélectionner la catégorie')->onlyOnForms(),
            AssociationField::new('brand', 'Sélectionner la marque')->onlyOnForms(),
            MoneyField::new('price', 'Prix')->setCurrency('MGA')->setStoredAsCents(false)->setNumDecimals(0),
            IntegerField::new('stock', 'Stock'),
            TextEditorField::new('description')->formatValue(fn($value, $entity) => $entity->getDescription()),
            CollectionField::new('images', 'Images')->setEntryType(ImageFormType::class),
        ];
    }
}
