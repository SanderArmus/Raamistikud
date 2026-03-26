<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cartService = new CartService();
        $items = $cartService->getItems($request);

        $totalCents = $cartService->getTotalAmountCents($request);

        // Convert for UI (stored as integer euros)
        $totalEuros = $totalCents / 100;

        return Inertia::render('shop/Cart', [
            'items' => array_map(function ($item) {
                /** @var Product $product */
                $product = $item['product'];

                return [
                    'product' => $product,
                    'quantity' => (int) $item['quantity'],
                    'line_total_euros' => ((int) $product->price * (int) $item['quantity']),
                ];
            }, $items),
            'total_euros' => $totalEuros,
        ]);
    }

    public function add(Request $request, CartService $cartService): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $productId = (int) $validated['product_id'];
        $quantity = (int) $validated['quantity'];

        // Ensure product exists and has stock
        $product = Product::query()->findOrFail($productId);
        if ((int) $product->stock_quantity <= 0) {
            return redirect()->route('cart.index')->with('error', 'This product is out of stock.');
        }

        $cartService->add($request, $productId, $quantity);

        return redirect()->route('cart.index')->with('success', 'Product added to cart.');
    }

    public function update(Request $request, CartService $cartService): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $productId = (int) $validated['product_id'];
        $quantity = (int) $validated['quantity'];

        $product = Product::query()->findOrFail($productId);

        // If user sets 0 -> remove, otherwise cap by stock in CartService
        if ($quantity > 0 && (int) $product->stock_quantity <= 0) {
            return redirect()->route('cart.index')->with('error', 'This product is out of stock.');
        }

        $cartService->update($request, $productId, $quantity);

        return redirect()->route('cart.index')->with('success', 'Cart updated.');
    }

    public function remove(Request $request, CartService $cartService, Product $product): RedirectResponse
    {
        $cartService->remove($request, (int) $product->id);

        return redirect()->route('cart.index')->with('success', 'Removed from cart.');
    }
}

