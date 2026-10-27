<?php

namespace App\Controller;

use App\Entity\Supplier;
use App\Form\SupplierType;
use App\Repository\SupplierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/supplier')]
final class SupplierController extends AbstractController
{
    #[Route(name: 'app_supplier_index', methods: ['GET'])]
    public function index(
        Request $request,
        SupplierRepository $supplierRepository
    ): Response {
        $qb = $supplierRepository->createQueryBuilder('s');

        $filters = [
            'id' => $request->query->get('id'),
            'name' => $request->query->get('name'),
            'contactName' => $request->query->get('contactName'),
            'email' => $request->query->get('email'),
            'phone' => $request->query->get('phone'),
            'address' => $request->query->get('address'),
        ];

        if (
            $filters['id'] !== null
            && $filters['id'] !== ''
            && ctype_digit((string) $filters['id'])
        ) {
            $qb->andWhere('s.id = :id')
                ->setParameter('id', (int) $filters['id']);
        }

        foreach ([
            'name',
            'contactName',
            'email',
            'phone',
            'address',
        ] as $field) {
            $value = $filters[$field];

            if ($value !== null && $value !== '') {
                $qb->andWhere("LOWER(s.$field) LIKE LOWER(:$field)")
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
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $totalPages = max(
            1,
            (int) ceil($totalItems / $itemsPerPage)
        );

        $page = min($page, $totalPages);

        $suppliers = $qb
            ->orderBy('s.id', 'ASC')
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage)
            ->getQuery()
            ->getResult();

        return $this->render('supplier/index.html.twig', [
            'suppliers' => $suppliers,
            'filters' => $filters,
            'itemsPerPage' => $itemsPerPage,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
        ]);
    }

    #[Route('/new', name: 'app_supplier_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $supplier = new Supplier();
        $form = $this->createForm(SupplierType::class, $supplier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($supplier);
            $entityManager->flush();

            return $this->redirectToRoute('app_supplier_index');
        }

        return $this->render('supplier/new.html.twig', [
            'supplier' => $supplier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_supplier_show', methods: ['GET'])]
    public function show(Supplier $supplier): Response
    {
        return $this->render('supplier/show.html.twig', [
            'supplier' => $supplier,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_supplier_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Supplier $supplier,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(SupplierType::class, $supplier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_supplier_index');
        }

        return $this->render('supplier/edit.html.twig', [
            'supplier' => $supplier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_supplier_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Supplier $supplier,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $supplier->getId(),
            $request->getPayload()->getString('_token')
        )) {
            $entityManager->remove($supplier);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_supplier_index');
    }
}