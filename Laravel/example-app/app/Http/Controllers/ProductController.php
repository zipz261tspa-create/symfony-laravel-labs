<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    
public function index(Request $request)
{
    $query = Product::query();

    $filters = [
        'id',
        'category_id',
        'name',
        'description',
        'price',
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
            'category_id',
            'price',
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);

        return response()->json(Product::create($validated), 201);
    }

    public function show(Product $product)
    {
        return $product;
    }

    public function update(Request $request, Product $product)
{
    $validated = $request->validate([
        'name' => 'sometimes|required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'sometimes|required|numeric|min:0',
        'category_id' => 'nullable|exists:categories,id',
    ]);

    $product->update($validated);

    return response()->json($product->fresh());
}

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }
}