<?php

namespace App\Controller;

use App\Entity\StockMovement;
use App\Form\StockMovementType;
use App\Repository\StockMovementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/stock/movement')]
final class StockMovementController extends AbstractController
{
    #[Route(name: 'app_stock_movement_index', methods: ['GET'])]
    public function index(
        Request $request,
        StockMovementRepository $stockMovementRepository
    ): Response {
        $qb = $stockMovementRepository->createQueryBuilder('sm');

        $filters = [
            'id' => $request->query->get('id'),
            'type' => $request->query->get('type'),
            'quantity' => $request->query->get('quantity'),
            'note' => $request->query->get('note'),
            'product' => $request->query->get('product'),
            'warehouse' => $request->query->get('warehouse'),
            'supplier' => $request->query->get('supplier'),
        ];

        if (
            $filters['id'] !== null
            && $filters['id'] !== ''
            && ctype_digit((string) $filters['id'])
        ) {
            $qb->andWhere('sm.id = :id')
                ->setParameter('id', (int) $filters['id']);
        }

        if ($filters['type'] !== null && $filters['type'] !== '') {
            $qb->andWhere('LOWER(sm.type) LIKE LOWER(:type)')
                ->setParameter('type', '%' . $filters['type'] . '%');
        }

        if (
            $filters['quantity'] !== null
            && $filters['quantity'] !== ''
            && is_numeric($filters['quantity'])
        ) {
            $qb->andWhere('sm.quantity = :quantity')
                ->setParameter('quantity', (int) $filters['quantity']);
        }

        if ($filters['note'] !== null && $filters['note'] !== '') {
            $qb->andWhere('LOWER(sm.note) LIKE LOWER(:note)')
                ->setParameter('note', '%' . $filters['note'] . '%');
        }

        if (
            $filters['product'] !== null
            && $filters['product'] !== ''
            && ctype_digit((string) $filters['product'])
        ) {
            $qb->andWhere('IDENTITY(sm.product) = :product')
                ->setParameter('product', (int) $filters['product']);
        }

        if (
            $filters['warehouse'] !== null
            && $filters['warehouse'] !== ''
            && ctype_digit((string) $filters['warehouse'])
        ) {
            $qb->andWhere('IDENTITY(sm.warehouse) = :warehouse')
                ->setParameter('warehouse', (int) $filters['warehouse']);
        }

        if (
            $filters['supplier'] !== null
            && $filters['supplier'] !== ''
            && ctype_digit((string) $filters['supplier'])
        ) {
            $qb->andWhere('IDENTITY(sm.supplier) = :supplier')
                ->setParameter('supplier', (int) $filters['supplier']);
        }

        $allowedPerPage = [5, 10, 25, 50, 100];
        $itemsPerPage = $request->query->getInt('itemsPerPage', 10);

        if (!in_array($itemsPerPage, $allowedPerPage, true)) {
            $itemsPerPage = 10;
        }

        $page = max(1, $request->query->getInt('page', 1));

        $countQb = clone $qb;

        $totalItems = (int) $countQb
            ->select('COUNT(sm.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $totalPages = max(
            1,
            (int) ceil($totalItems / $itemsPerPage)
        );

        $page = min($page, $totalPages);

        $stockMovements = $qb
            ->orderBy('sm.id', 'ASC')
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage)
            ->getQuery()
            ->getResult();

        return $this->render('stock_movement/index.html.twig', [
            'stock_movements' => $stockMovements,
            'filters' => $filters,
            'itemsPerPage' => $itemsPerPage,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
        ]);
    }

    #[Route('/new', name: 'app_stock_movement_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $stockMovement = new StockMovement();
        $form = $this->createForm(StockMovementType::class, $stockMovement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($stockMovement);
            $entityManager->flush();

            return $this->redirectToRoute(
                'app_stock_movement_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('stock_movement/new.html.twig', [
            'stock_movement' => $stockMovement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_stock_movement_show', methods: ['GET'])]
    public function show(StockMovement $stockMovement): Response
    {
        return $this->render('stock_movement/show.html.twig', [
            'stock_movement' => $stockMovement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_stock_movement_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        StockMovement $stockMovement,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(StockMovementType::class, $stockMovement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute(
                'app_stock_movement_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('stock_movement/edit.html.twig', [
            'stock_movement' => $stockMovement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_stock_movement_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        StockMovement $stockMovement,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $stockMovement->getId(),
            $request->getPayload()->getString('_token')
        )) {
            $entityManager->remove($stockMovement);
            $entityManager->flush();
        }

        return $this->redirectToRoute(
            'app_stock_movement_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }
}