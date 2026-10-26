<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    
public function index(Request $request)
{
    $query = Warehouse::with('stockMovements');

    $filters = [
        'id',
        'name',
        'location',
        'description',
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
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        return response()->json(Warehouse::create($data), 201);
    }

    public function show(Warehouse $warehouse)
    {
        return $warehouse->load('stockMovements');
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'location' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $warehouse->update($data);

        return $warehouse;
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();

        return response()->json(['message' => 'Warehouse deleted successfully']);
    }
}