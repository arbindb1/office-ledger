<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemsController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');

        $items = Item::query()
            ->when($q, fn($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:items,name'],
            'default_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $item = Item::create([
            'name' => $data['name'],
            'default_price' => round((float)$data['default_price'], 2),
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json(['success' => true, 'data' => $item], 201);
    }

    public function update(Request $request, int $itemId)
{
    $item = Item::findOrFail($itemId);

    $data = $request->validate([
        'name' => ['nullable', 'string', 'max:255', 'unique:items,name,' . $item->id],
        'default_price' => ['nullable', 'numeric', 'min:0'],
        'is_active' => ['nullable', 'boolean'],
    ]);

    if (isset($data['default_price'])) {
        $data['default_price'] = round((float)$data['default_price'], 2);
    }

    $item->update($data);

    return response()->json(['success' => true, 'data' => $item->fresh()]);
}

public function deactivate(int $itemId)
{
    $item = Item::findOrFail($itemId);
    $item->update(['is_active' => false]);

    return response()->json(['success' => true, 'data' => $item->fresh()]);
}

}
