<?php

namespace App\Http\Controllers;

use App\Models\Component;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComponentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Component::query();

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $components = $query->orderBy('name')->paginate((int) $request->integer('per_page', 15));

        return response()->json($components);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:components,code'],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
        ]);

        $component = Component::query()->create($data);

        return response()->json($component, 201);
    }

    public function show(Component $component): JsonResponse
    {
        return response()->json($component->load('products'));
    }

    public function update(Request $request, Component $component): JsonResponse
    {
        $data = $request->validate([
            'code' => ['sometimes', 'string', 'max:50', "unique:components,code,{$component->id}"],
            'name' => ['sometimes', 'string', 'max:255'],
            'unit' => ['sometimes', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
        ]);

        $component->update($data);

        return response()->json($component->fresh());
    }

    public function destroy(Component $component): JsonResponse
    {
        $component->delete();

        return response()->json(['message' => 'Komponen dihapus']);
    }
}
