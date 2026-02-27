<?php

namespace App\Http\Controllers;

use App\Mail\PaymentConfirmation;
use App\Models\Booking;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret = (string) config('services.stripe.webhook_secret');

        try {
            if ($secret !== '' && $signature) {
                $event = \Stripe\Webhook::constructEvent($payload, $signature, $secret);
            } else {
                $event = json_decode($payload, false, 512, JSON_THROW_ON_ERROR);
            }
        } catch (\Throwable $e) {
            return response('Invalid payload', 400);
        }

        if (($event->type ?? null) === 'payment_intent.succeeded') {
            $intent = $event->data->object ?? null;
            $bookingId = $intent->metadata->booking_id ?? null;

            if ($bookingId) {
                $booking = Booking::with(['customer', 'service'])->find($bookingId);

                if ($booking && $booking->payment_status !== 'paid') {
                    $booking->update([
                        'payment_status' => 'paid',
                        'payment_method' => 'stripe',
                        'paid_at' => now(),
                        'payment_notes' => 'Stripe Webhook PaymentIntent: ' . ($intent->id ?? ''),
                    ]);

                    Mail::to($booking->customer->email)->send(new PaymentConfirmation($booking));
                    NotificationService::paymentReceived($booking);
                }
            }
        }

        return response('OK', 200);
    }
}
