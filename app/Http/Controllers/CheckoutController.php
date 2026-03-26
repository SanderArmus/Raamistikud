<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
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
            // Log the underlying Stripe error for easier debugging in production.
            Log::error('Stripe session creation failed', [
                'message' => $e->getMessage(),
                'order_id' => $order->id ?? null,
            ]);
            $order->update(['status' => 'failed']);

            return redirect()
                ->route('checkout.show')
                ->with('error', 'Stripe session creation failed: ' . $e->getMessage());
        }

        $order->update([
            'stripe_checkout_session_id' => (string) $session->id,
        ]);

        // Inertia POST requests don't always follow cross-origin redirects reliably.
        // `Inertia::location()` forces a real browser navigation to Stripe Checkout.
        return Inertia::location((string) $session->url);
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

        // If webhook hasn't arrived yet, confirm payment status directly from Stripe.
        if ($order && $status !== 'succeeded') {
            $stripeKey = (string) config('services.stripe.secret', env('STRIPE_SECRET_KEY', ''));
            if ($stripeKey !== '') {
                try {
                    $client = new StripeClient($stripeKey);
                    $session = $client->checkout->sessions->retrieve($sessionId, []);

                    $paymentStatus = (string) ($session->payment_status ?? '');
                    if ($paymentStatus === 'paid') {
                        $order->update(['status' => 'succeeded']);
                        $status = 'succeeded';
                    }
                } catch (\Throwable $e) {
                    // Keep current status; user still sees a helpful message below.
                }
            }
        }

        if ($status === 'succeeded') {
            (new CartService())->clear($request);
        }

        $message = $status === 'succeeded'
            ? 'Payment successful!'
            : 'Payment status is not succeeded yet. (Try again shortly.)';

        if ($status === 'succeeded') {
            $request->session()->flash('success', 'Payment successful! Your cart is now empty.');
        }

        return Inertia::render('shop/Success', [
            'status' => $status,
            'message' => $message,
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

