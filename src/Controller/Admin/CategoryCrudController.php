<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[AdminRoute('catégories')]
class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setPageTitle(Crud::PAGE_INDEX, '<h1>Liste des catégories</h1>')
            ->setPaginatorPageSize(15)
            ->setPageTitle(Crud::PAGE_NEW, '<h1>Nouvelle catégorie</h1>')
            ->setPageTitle(
                Crud::PAGE_EDIT,
                fn(
                    Category $category
                ) => "<h1>Modification de la catégorie <span class='text-warning'>$category</span></h1>"
            );
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $action) => $action->setIcon('fa fa-plus')->setLabel('Nouvelle catégorie'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom'),
            SlugField::new('slug', 'Slug')->onlyOnForms()->setTargetFieldName('name'),
        ];
    }
}
