<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()->withCount('productComponents');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($category = $request->string('category')->toString()) {
            $query->where('category', $category);
        }

        $perPage = (int) $request->integer('per_page', 15);

        $products = $query->orderBy('name')->paginate($perPage);

        if ($request->boolean('with_components')) {
            $products->load('components');
        }

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:products,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:draft,active,inactive'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $product = Product::query()->create([
            ...$data,
            'status' => $data['status'] ?? 'active',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return response()->json($product->fresh('components'), 201);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load('components');

        return response()->json($product);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'code' => ['sometimes', 'string', 'max:50', "unique:products,code,{$product->id}"],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:draft,active,inactive'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $product->fill($data);
        $product->updated_by = $user->id;
        $product->save();

        return response()->json($product->fresh('components'));
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Produk dihapus']);
    }
}
