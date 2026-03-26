<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $secret = (string) env('STRIPE_WEBHOOK_SECRET', '');
        if ($secret === '') {
            return response('Webhook secret not configured.', 500);
        }

        $payload = $request->getContent();
        $sigHeader = (string) $request->header('Stripe-Signature', '');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response('Invalid signature', 400);
        }

        // We mainly care about checkout session completion.
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $sessionId = (string) ($session->id ?? '');
            $orderId = (int) (($session->metadata->order_id ?? 0));

            $order = null;
            if ($orderId > 0) {
                $order = Order::query()->find($orderId);
            }
            if (! $order && $sessionId !== '') {
                $order = Order::query()->where('stripe_checkout_session_id', $sessionId)->first();
            }

            if ($order) {
                $order->update([
                    'status' => 'succeeded',
                    'stripe_checkout_session_id' => $order->stripe_checkout_session_id ?: $sessionId,
                ]);
            }
        }

        if (in_array($event->type, ['checkout.session.async_payment_failed', 'payment_intent.payment_failed'], true)) {
            $obj = $event->data->object;
            $sessionId = (string) ($obj->id ?? '');

            if ($sessionId !== '') {
                Order::query()
                    ->where('stripe_checkout_session_id', $sessionId)
                    ->update(['status' => 'failed']);
            }
        }

        return response('ok', 200);
    }
}

