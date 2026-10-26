<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    
public function index(Request $request)
{
    $query = Category::with('products');

    $filters = [
        'id',
        'name',
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
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        return response()->json(Category::create($data), 201);
    }

    public function show(Category $category)
    {
        return $category->load('products');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $category->update($data);

        return $category;
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}