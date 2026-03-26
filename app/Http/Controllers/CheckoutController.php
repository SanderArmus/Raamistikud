<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Stripe\StripeClient;

class CheckoutController extends Controller
{
    public function show(Request $request)
    {
        $cartService = new CartService();
        $items = $cartService->getItems($request);
        $totalCents = $cartService->getTotalAmountCents($request);

        if ($items === []) {
            return redirect()->route('shop.index')->with('error', 'Cart is empty.');
        }

        // Convert for UI (stored as integer euros)
        $totalEuros = $totalCents / 100;

        $user = $request->user();

        return Inertia::render('shop/Checkout', [
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
            'prefill' => [
                'first_name' => $user ? explode(' ', (string) $user->name)[0] : '',
                'last_name' => $user ? implode(' ', array_slice(explode(' ', (string) $user->name), 1)) : '',
                'email' => $user ? (string) $user->email : '',
                'phone' => '',
            ],
        ]);
    }

    public function createStripeSession(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
        ]);

        $cartService = new CartService();
        $items = $cartService->getItems($request);
        if ($items === []) {
            return redirect()->route('shop.index')->with('error', 'Cart is empty.');
        }

        $stripeKey = (string) config('services.stripe.secret', env('STRIPE_SECRET_KEY', ''));
        if ($stripeKey === '') {
            return redirect()
                ->route('checkout.show')
                ->with('error', 'Stripe is not configured yet (missing STRIPE_SECRET_KEY).');
        }

        $order = $this->createPendingOrder($request, $validated, $cartService);

        $client = new StripeClient($stripeKey);

        $currency = (string) (env('STRIPE_CURRENCY', 'eur') ?: 'eur');

        $lineItems = [];
        foreach ($items as $item) {
            /** @var Product $product */
            $product = $item['product'];
            $quantity = (int) $item['quantity'];

            // products.price is integer euros -> convert to cents
            $unitAmount = (int) $product->price * 100;

            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => (string) $product->name,
                        'description' => (string) $product->description,
                        'images' => $product->image_url ? [(string) $product->image_url] : [],
                    ],
                    'unit_amount' => $unitAmount,
                ],
                'quantity' => $quantity,
            ];
        }

        $successUrl = route('checkout.success', [], true) . '?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = route('checkout.cancel', [], true);

        try {
            $session = $client->checkout->sessions->create([
                'mode' => 'payment',
                'line_items' => $lineItems,
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => [
                    'order_id' => (string) $order->id,
                ],
                'customer_email' => $validated['email'],
            ]);
        } catch (\Throwable $e) {
            $order->update(['status' => 'failed']);

            return redirect()
                ->route('checkout.show')
                ->with('error', 'Stripe session creation failed. Please try again later.');
        }

        $order->update([
            'stripe_checkout_session_id' => (string) $session->id,
        ]);

        return redirect()->away((string) $session->url);
    }

    public function success(Request $request)
    {
        $sessionId = (string) $request->query('session_id', '');
        if ($sessionId === '') {
            return Inertia::render('shop/Success', [
                'status' => 'failed',
                'message' => 'Missing session id.',
            ]);
        }

        $order = Order::query()
            ->where('stripe_checkout_session_id', $sessionId)
            ->first();

        $status = $order?->status ?? 'failed';

        if ($status === 'succeeded') {
            (new CartService())->clear($request);
        }

        return Inertia::render('shop/Success', [
            'status' => $status,
            'message' => $status === 'succeeded'
                ? 'Payment successful!'
                : 'Payment status is not succeeded yet. (Try again shortly.)',
        ]);
    }

    public function cancel()
    {
        return Inertia::render('shop/Cancel', [
            'status' => 'failed',
            'message' => 'Payment was cancelled or failed. Your cart was kept.',
        ]);
    }

    /**
     * Create an order + order_items snapshot from the current cart.
     * Stripe integration will update the order with stripe_checkout_session_id.
     */
    private function createPendingOrder(Request $request, array $billing, CartService $cartService): Order
    {
        $items = $cartService->getItems($request);
        $totalCents = $cartService->getTotalAmountCents($request);

        $order = Order::query()->create([
            'user_id' => $request->user()?->id,
            'first_name' => $billing['first_name'],
            'last_name' => $billing['last_name'],
            'email' => $billing['email'],
            'phone' => $billing['phone'],
            'total_amount' => $totalCents,
            'status' => 'pending',
            'stripe_checkout_session_id' => null,
        ]);

        foreach ($items as $item) {
            /** @var Product $product */
            $product = $item['product'];
            $quantity = (int) $item['quantity'];

            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => (int) $product->id,
                'name_snapshot' => (string) $product->name,
                'unit_price_snapshot' => (int) $product->price * 100,
                'quantity' => $quantity,
            ]);
        }

        return $order;
    }
}

