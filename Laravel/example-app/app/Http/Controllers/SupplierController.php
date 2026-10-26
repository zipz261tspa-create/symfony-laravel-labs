<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
        
public function index(Request $request)
{
    $query = Supplier::query();

    $filters = [
        'id',
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'created_at',
        'updated_at',
    ];

    foreach ($filters as $field) {
        $value = $request->query($field);

        if ($value === null || $value === '') {
            continue;
        }

        if (in_array($field, [
            'id',
            'created_at',
            'updated_at',
        ], true)) {
            $query->where($field, $value);
        } else {
            $query->where($field, 'like', '%' . $value . '%');
        }
    }

    $perPage = (int) $request->query('itemsPerPage', 10);

    $perPage = in_array($perPage, [10, 25, 50, 100], true)
        ? $perPage
        : 10;

    return $query
        ->orderBy('id')
        ->paginate($perPage)
        ->withQueryString();
}


    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        return response()->json(Supplier::create($data), 201);
    }

    public function show(Supplier $supplier)
    {
        return $supplier->load('stockMovements');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        $supplier->update($data);

        return $supplier;
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted successfully']);
    }
}