<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InvoiceController extends AbstractController
{
    #[Route('admin/facture/{id}', name: 'app_admin_invoice')]
    public function index(Order $order): Response
    {
        $options = new Options();
        $options->setDefaultFont('Helvetica');

        $dompdf = new Dompdf($options);
        $view = $this->renderView('admin/invoice/index.html.twig', ['order' => $order]);
        $dompdf->loadHtml($view);
        $dompdf->render();

        return new Response($dompdf->output(), Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="invoice-'.$order->getId().'-'.$order->getFirstName().'-'
                .$order->getLastName().'.pdf"',
        ]);
    }
}
