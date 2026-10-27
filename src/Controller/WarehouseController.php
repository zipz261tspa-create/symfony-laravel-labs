<?php

namespace App\Controller;

use App\Entity\Warehouse;
use App\Form\WarehouseType;
use App\Repository\WarehouseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/warehouse')]
final class WarehouseController extends AbstractController
{
    #[Route(name: 'app_warehouse_index', methods: ['GET'])]
    public function index(
        Request $request,
        WarehouseRepository $warehouseRepository
    ): Response {
        $qb = $warehouseRepository->createQueryBuilder('w');

        $filters = [
            'id' => $request->query->get('id'),
            'name' => $request->query->get('name'),
            'location' => $request->query->get('location'),
            'description' => $request->query->get('description'),
        ];

        if (
            $filters['id'] !== null
            && $filters['id'] !== ''
            && ctype_digit((string) $filters['id'])
        ) {
            $qb->andWhere('w.id = :id')
                ->setParameter('id', (int) $filters['id']);
        }

        foreach (['name', 'location', 'description'] as $field) {
            $value = $filters[$field];

            if ($value !== null && $value !== '') {
                $qb->andWhere("LOWER(w.$field) LIKE LOWER(:$field)")
                    ->setParameter($field, '%' . $value . '%');
            }
        }

        $allowedPerPage = [5, 10, 25, 50, 100];
        $itemsPerPage = $request->query->getInt('itemsPerPage', 10);

        if (!in_array($itemsPerPage, $allowedPerPage, true)) {
            $itemsPerPage = 10;
        }

        $page = max(1, $request->query->getInt('page', 1));

        $countQb = clone $qb;

        $totalItems = (int) $countQb
            ->select('COUNT(w.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $totalPages = max(
            1,
            (int) ceil($totalItems / $itemsPerPage)
        );

        $page = min($page, $totalPages);

        $warehouses = $qb
            ->orderBy('w.id', 'ASC')
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage)
            ->getQuery()
            ->getResult();

        return $this->render('warehouse/index.html.twig', [
            'warehouses' => $warehouses,
            'filters' => $filters,
            'itemsPerPage' => $itemsPerPage,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
        ]);
    }

    #[Route('/new', name: 'app_warehouse_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $warehouse = new Warehouse();
        $form = $this->createForm(WarehouseType::class, $warehouse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($warehouse);
            $entityManager->flush();

            return $this->redirectToRoute(
                'app_warehouse_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('warehouse/new.html.twig', [
            'warehouse' => $warehouse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_warehouse_show', methods: ['GET'])]
    public function show(Warehouse $warehouse): Response
    {
        return $this->render('warehouse/show.html.twig', [
            'warehouse' => $warehouse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_warehouse_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Warehouse $warehouse,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(WarehouseType::class, $warehouse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute(
                'app_warehouse_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('warehouse/edit.html.twig', [
            'warehouse' => $warehouse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_warehouse_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Warehouse $warehouse,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $warehouse->getId(),
            $request->getPayload()->getString('_token')
        )) {
            $entityManager->remove($warehouse);
            $entityManager->flush();
        }

        return $this->redirectToRoute(
            'app_warehouse_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }
}