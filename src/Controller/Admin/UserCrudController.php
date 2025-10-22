<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminRoute('utilisateurs')]
class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setPageTitle(Crud::PAGE_INDEX, 'Liste des utilisateurs')->setPageTitle(
            Crud::PAGE_DETAIL,
            fn($user) => '<span class="text-primary">'.$user->getUsername().'</span>'
        );
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(
                Crud::PAGE_INDEX,
                Action::DETAIL,
                fn($action) => $action->setIcon('fa fa-eye')->setCssClass('text-success fw-semibold')
            )
            ->remove(Crud::PAGE_DETAIL, Action::EDIT);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            ImageField::new('avatar', 'Photo de profil')->setBasePath('img/uploads/avatar/')->setUploadDir(
                'img/uploads/avatar'
            ),
            IdField::new('id'),
            TextField::new('lastname', 'Nom'),
            TextField::new('firstname', 'Prénom'),
            EmailField::new('email', 'Email'),
            BooleanField::new('isVerified', 'Vérifier')->renderAsSwitch(false),
            ArrayField::new('roles', 'Roles')->hideOnForm()->formatValue(
                fn($value, $user) => in_array(
                    'ROLE_ADMIN',
                    $user->getRoles(),
                    true
                ) ? "<span class='text-blue-600'>Administrateur</span>" : "Utilisateur"
            ),
            DateTimeField::new('createdAt', 'Créé le')->hideOnForm()->setFormat('d-MMMM-Y'),
            DateTimeField::new('updatedAt', 'Modifié le')->hideOnForm()->setFormat('d-MMMM-Y'),
        ];
    }
}
