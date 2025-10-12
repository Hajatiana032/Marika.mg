<?php

namespace App\Controller\Admin;

use App\Entity\Testimonial;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PhpParser\Node\Expr\Yield_;

#[AdminRoute('témoignages')]
class TestimonialCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Testimonial::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setPageTitle(Crud::PAGE_INDEX, 'Liste des témoignages')
            ->setPageTitle(Crud::PAGE_DETAIL, fn(Testimonial $testimonial) => "Témoignage de <span class='text-success'>{$testimonial->getUser()}</span>");
    }

    public function configureFields(string $pageName): iterable
    {

        return [
            TextareaField::new('content')->setLabel('Contenu du témoignage'),
            BooleanField::new('isVerified')->setLabel('Vérifié')->renderAsSwitch(false),
            TextField::new('user')->setLabel('Utilisateur')->hideOnForm(),
            DateTimeField::new('createdAt')->setLabel('Créé le')->setFormat('d-M-Y')->hideOnForm(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, 'detail')
            ->update(Crud::PAGE_INDEX, 'detail', fn($action) => $action->setIcon('fa fa-eye')->setLabel('<span class="text-success">Détails</span>'))
            ->update(Crud::PAGE_INDEX, 'new', fn($action) => $action->setIcon('fa fa-plus')->setLabel('Nouveau témoignage'))
        ;
    }
}
