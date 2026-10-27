<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route; 

#[Route('/category')]
final class CategoryController extends AbstractController
{
    #[Route(name: 'app_category_index', methods: ['GET'])]
    public function index(Request $request, CategoryRepository $categoryRepository): Response
    {
        $qb = $categoryRepository->createQueryBuilder('c');

        $id = $request->query->get('id');
        $name = $request->query->get('name');
        $description = $request->query->get('description');

        if ($id !== null && $id !== '' && ctype_digit((string) $id)) {
            $qb->andWhere('c.id = :id')
               ->setParameter('id', (int) $id);
        }

        if ($name !== null && $name !== '') {
            $qb->andWhere('LOWER(c.name) LIKE LOWER(:name)')
               ->setParameter('name', '%' . $name . '%');
        }

        if ($description !== null && $description !== '') {
            $qb->andWhere('LOWER(c.description) LIKE LOWER(:description)')
               ->setParameter('description', '%' . $description . '%');
        }

        $allowedPerPage = [5, 10, 25, 50, 100];
        $itemsPerPage = (int) $request->query->get('itemsPerPage', 10);

        if (!in_array($itemsPerPage, $allowedPerPage, true)) {
            $itemsPerPage = 10;
        }

        $page = max(1, $request->query->getInt('page', 1));

        $countQb = clone $qb;
        $totalItems = (int) $countQb
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $totalPages = max(1, (int) ceil($totalItems / $itemsPerPage));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $categories = $qb
            ->orderBy('c.id', 'ASC')
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage)
            ->getQuery()
            ->getResult();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
            'filters' => [
                'id' => $id,
                'name' => $name,
                'description' => $description,
            ],
            'itemsPerPage' => $itemsPerPage,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
        ]);
    }


    #[Route('/new', name: 'app_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
