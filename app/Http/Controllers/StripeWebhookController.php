<?php

namespace App\Http\Controllers;

use App\Payments\CheckoutSession;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, CheckoutService $checkout): JsonResponse
    {
        $secret = config('services.stripe.webhook_secret');

        if (blank($secret)) {
            Log::error('Stripe webhook received but STRIPE_WEBHOOK_SECRET is not configured.');

            return response()->json(['error' => 'Webhook not configured.'], 500);
        }

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
                $secret,
                config('services.stripe.webhook_tolerance', Webhook::DEFAULT_TOLERANCE),
            );
        } catch (SignatureVerificationException|UnexpectedValueException) {
            return response()->json(['error' => 'Invalid signature.'], 400);
        }

        $object = $event->data->object->toArray();

        match ($event->type) {
            'checkout.session.completed',
            'checkout.session.async_payment_succeeded' => $checkout->confirmCheckoutSession(CheckoutSession::fromStripe($object)),
            'checkout.session.async_payment_failed',
            'checkout.session.expired' => $checkout->failCheckoutSession(CheckoutSession::fromStripe($object)),
            'charge.refunded' => $checkout->refundFromCharge($object),
            'charge.dispute.closed' => $checkout->revokeForLostDispute($object),
            default => null,
        };

        return response()->json(['received' => true]);
    }
}
