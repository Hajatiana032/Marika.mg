<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

#[AdminRoute('commandes')]
class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->overrideTemplate('crud/index', 'admin/order/index.html.twig')
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des commandes')
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $delivered = Action::new('delivered', 'Marquer comme livré', 'fa fa-check-circle')
            ->asTextLink()
            ->addCssClass('link-success')
            ->linkToCrudAction('delivered')->displayIf(fn(Order $order) => ! $order->isDelivered());

        $invoice = Action::new('invoice', 'Facturer', 'fa fa-file-invoice')->addCssClass('link-secondary')->asTextLink()
            ->linkToUrl(fn(Order $order) => '/admin/facture/'.$order->getId())->setHtmlAttributes(['target' => '_blank']
            );

        return $actions->remove(Crud::PAGE_INDEX, Action::NEW)
            ->add(Crud::PAGE_INDEX, $delivered)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->add(Crud::PAGE_INDEX, $invoice)
            ->reorder(Crud::PAGE_INDEX, ['delivered', Action::DELETE]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \DateMalformedStringException
     */
    public function delivered(AdminContext $context): Response
    {
        $entityManager = $this->container->get('doctrine')->getManager();
        $order = $context->getEntity()->getInstance();
        $order->setIsDelivered(true)->setDeliveredAt(
            new \DateTimeImmutable(
                'now', new \DateTimeZone
                (
                    'Indian/Antananarivo'
                )
            )
        );
        $entityManager->flush($order);

        return $this->redirectToRoute('admin_order_index');
    }

    public function configureFilters(
        Filters $filters
    ): Filters {
        return $filters
            ->add(DateTimeFilter::new('createdAt', 'Date de création'))
            ->add(BooleanFilter::new('isDelivered', 'Livraison effectué'));
    }

//    public function configureFields(string $pageName): iterable
//    {
//        return [
//        ];
//    }
}
