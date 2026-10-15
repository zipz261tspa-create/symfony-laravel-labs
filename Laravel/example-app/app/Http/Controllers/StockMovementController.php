<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StockMovementController extends Controller
{
    public function index()
    {
        return StockMovement::with(['product', 'warehouse', 'supplier'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'type' => ['required', Rule::in(['receipt', 'issue'])],
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string',
        ]);

        return response()->json(StockMovement::create($data), 201);
    }

    public function show(StockMovement $stockMovement)
    {
        return $stockMovement->load(['product', 'warehouse', 'supplier']);
    }

    public function update(Request $request, StockMovement $stockMovement)
    {
        $data = $request->validate([
            'product_id' => 'sometimes|required|exists:products,id',
            'warehouse_id' => 'sometimes|required|exists:warehouses,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'type' => ['sometimes', 'required', Rule::in(['receipt', 'issue'])],
            'quantity' => 'sometimes|required|integer|min:1',
            'note' => 'nullable|string',
        ]);

        $stockMovement->update($data);

        return $stockMovement->load(['product', 'warehouse', 'supplier']);
    }

    public function destroy(StockMovement $stockMovement)
    {
        $stockMovement->delete();

        return response()->json(['message' => 'Stock movement deleted successfully']);
    }
}