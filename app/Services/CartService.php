<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Request;

class CartService
{
    public function getCart(Request $request): array
    {
        $cart = $request->session()->get('cart', []);

        if (! is_array($cart)) {
            return [];
        }

        // Normalize to [productId => quantity]
        $normalized = [];
        foreach ($cart as $productId => $qty) {
            $pid = (int) $productId;
            $q = (int) $qty;
            if ($pid > 0 && $q > 0) {
                $normalized[$pid] = $q;
            }
        }

        return $normalized;
    }

    public function add(Request $request, int $productId, int $quantity): array
    {
        $cart = $this->getCart($request);
        $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;

        $cart = $this->normalizeQuantities($request, $cart);

        $request->session()->put('cart', $cart);

        return $cart;
    }

    public function update(Request $request, int $productId, int $quantity): array
    {
        $cart = $this->getCart($request);

        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $quantity;
        }

        $cart = $this->normalizeQuantities($request, $cart);

        $request->session()->put('cart', $cart);

        return $cart;
    }

    public function remove(Request $request, int $productId): array
    {
        $cart = $this->getCart($request);
        unset($cart[$productId]);
        $request->session()->put('cart', $cart);

        return $cart;
    }

    public function clear(Request $request): void
    {
        $request->session()->forget('cart');
    }

    public function getItems(Request $request): array
    {
        $cart = $this->getCart($request);
        $productIds = array_keys($cart);

        if ($productIds === []) {
            return [];
        }

        $products = Product::query()->whereIn('id', $productIds)->get()->keyBy('id');

        $items = [];
        foreach ($cart as $productId => $quantity) {
            if (! $products->has($productId)) {
                continue;
            }

            $items[] = [
                'product' => $products->get($productId),
                'quantity' => $quantity,
            ];
        }

        return $items;
    }

    public function getTotalAmountCents(Request $request): int
    {
        $items = $this->getItems($request);

        $total = 0;
        foreach ($items as $item) {
            /** @var Product $product */
            $product = $item['product'];
            $quantity = (int) $item['quantity'];

            // products.price is stored as integer euros
            $unitCents = (int) $product->price * 100;
            $total += $unitCents * $quantity;
        }

        return $total;
    }

    private function normalizeQuantities(Request $request, array $cart): array
    {
        if ($cart === []) {
            return [];
        }

        $products = Product::query()->whereIn('id', array_keys($cart))->get()->keyBy('id');

        $normalized = [];
        foreach ($cart as $productId => $quantity) {
            if (! $products->has($productId)) {
                continue;
            }

            $product = $products->get($productId);
            $max = (int) $product->stock_quantity;

            if ($max <= 0) {
                continue;
            }

            $q = (int) $quantity;
            $q = max(1, $q);
            $q = min($q, $max);

            $normalized[(int) $productId] = $q;
        }

        return $normalized;
    }
}

