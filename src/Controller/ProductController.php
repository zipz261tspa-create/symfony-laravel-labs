<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/product')]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function index(
        Request $request,
        ProductRepository $productRepository
    ): Response {
        $id = trim($request->query->getString('id'));
        $name = trim($request->query->getString('name'));
        $description = trim($request->query->getString('description'));
        $price = trim($request->query->getString('price'));
        $categoryId = trim($request->query->getString('category_id'));

        $allowedPerPage = [5, 10, 25, 50, 100];
        $itemsPerPage = $request->query->getInt('itemsPerPage', 10);

        if (!in_array($itemsPerPage, $allowedPerPage, true)) {
            $itemsPerPage = 10;
        }

        $page = max(1, $request->query->getInt('page', 1));

        $queryBuilder = $productRepository->createQueryBuilder('p');

        if ($id !== '' && ctype_digit($id)) {
            $queryBuilder
                ->andWhere('p.id = :id')
                ->setParameter('id', (int) $id);
        }

        if ($name !== '') {
            $queryBuilder
                ->andWhere('LOWER(p.name) LIKE LOWER(:name)')
                ->setParameter('name', '%' . $name . '%');
        }

        if ($description !== '') {
            $queryBuilder
                ->andWhere('LOWER(p.description) LIKE LOWER(:description)')
                ->setParameter('description', '%' . $description . '%');
        }

        if ($price !== '' && is_numeric($price)) {
            $queryBuilder
                ->andWhere('p.price = :price')
                ->setParameter('price', $price);
        }
        
        if ($categoryId !== '' && ctype_digit($categoryId)) {
        $queryBuilder
        ->andWhere('IDENTITY(p.category) = :categoryId')
        ->setParameter('categoryId', (int) $categoryId);
        }
    
        $queryBuilder
            ->orderBy('p.id', 'ASC')
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage);

        $paginator = new Paginator($queryBuilder->getQuery());
        $totalItems = count($paginator);
        $totalPages = max(1, (int) ceil($totalItems / $itemsPerPage));

        // Якщо користувач указав сторінку за межами результатів,
        // показуємо останню доступну сторінку.
        if ($page > $totalPages) {
            $page = $totalPages;

            $queryBuilder
                ->setFirstResult(($page - 1) * $itemsPerPage)
                ->setMaxResults($itemsPerPage);

            $paginator = new Paginator($queryBuilder->getQuery());
        }

        return $this->render('product/index.html.twig', [
            'products' => $paginator,
            'filters' => [
                'id' => $id,
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'category_id' => $categoryId,
            ],
            'page' => $page,
            'itemsPerPage' => $itemsPerPage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute(
                'app_product_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Product $product,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute(
                'app_product_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Product $product,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $product->getId(),
            $request->getPayload()->getString('_token')
        )) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute(
            'app_product_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }
}
