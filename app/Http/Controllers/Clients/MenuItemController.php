<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function getByCategory(Request $request, $id_cate)
    {
        // $perPage = 12;
        // $page = $request->query('page', 1);

        $menuItems = MenuItem::where('id_cate', $id_cate)
            ->where('status', '=', 0)
            ->paginate(12)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $menuItems->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'id_cate' => $item->id_cate,
                    'image' => $item->image
                ];
            }),
            'pagination' => [
                'current_page' => $menuItems->currentPage(),
                'last_page' => $menuItems->lastPage(),
                'per_page' => $menuItems->perPage(),
                'total' => $menuItems->total(),
            ]
        ]);
    }
}