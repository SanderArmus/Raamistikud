<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;

class ShopController extends Controller
{
    public function index()
    {
        // Keep it simple: show a fixed catalog (factory/seeder should ensure >= 9 products)
        $products = Product::query()
            ->select(['id', 'name', 'price', 'description', 'image_url', 'stock_quantity'])
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        return Inertia::render('shop/Index', [
            'products' => $products,
        ]);
    }
}

