<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/catégories', 'app_admin_category')]
final class CategoryController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SluggerInterface $slugger,
    ) {
    }

    #[Route('', name: '')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('admin/category/index.html.twig', [
            'current_menu' => 'admin_category',
            'categories' => $categoryRepository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/ajout', '_new')]
    public function new(Request $request): Response
    {
        $category = new Category();

        return $this->handleForm(
            request: $request,
            entity: $category,
            template: 'admin/category/new.html.twig',
            flash: [
                'type' => 'success',
                'color' => 'white',
                'message' => fn(Category $c) => 'La catégorie '.$c->getName().' a été ajoutée.',
            ]
        );
    }

    private function handleForm(
        Request $request,
        $entity,
        string $template,
        array $flash
    ): Response {
        $form = $this->createForm(CategoryType::class, $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entity->setSlug($this->slugger->slug($entity->getName())->lower());
            $this->em->persist($entity);
            $this->em->flush();

            $this->addFlash($flash['type'], [
                'color' => $flash['color'],
                'message' => $flash['message']($entity),
            ]);

            return $this->redirectToRoute('app_admin_category');
        }

        return $this->render($template, [
            'current_menu' => 'admin_category',
            'form' => $form,
            'category' => $entity,
        ]);
    }

    #[Route('/modification/{slug}', '_update')]
    public function update(Request $request, Category $category): Response
    {
        return $this->handleForm(
            request: $request,
            entity: $category,
            template: 'admin/category/update.html.twig',
            flash: [
                'type' => 'warning',
                'color' => 'dark',
                'message' => fn(Category $c) => 'La catégorie '.$c->getName().' a été modifiée.',
            ]
        );
    }

    #[Route('/supprimer/{slug}', '_delete')]
    public function delete(Category $category): Response
    {
        $this->em->remove($category);
        $this->em->flush();
        $this->addFlash('danger', [
            'color' => 'white',
            'message' => "La catégorie {$category->getName()} a été supprimées.",
        ]);

        return $this->redirectToRoute('app_admin_category');
    }
}
