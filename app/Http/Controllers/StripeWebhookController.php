<?php

namespace App\Http\Controllers;

use App\Models\PackageApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    /**
     * Handle Stripe webhook events
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            // Verify webhook signature (if secret is configured)
            if ($webhookSecret) {
                $event = \Stripe\Webhook::constructEvent(
                    $payload,
                    $sigHeader,
                    $webhookSecret
                );
            } else {
                // For testing without webhook secret
                $event = json_decode($payload, true);
                Log::warning('⚠️ Stripe webhook called without signature verification');
            }

            Log::info('📨 Stripe webhook received: ' . $event['type']);

            // Handle the event
            switch ($event['type']) {
                case 'checkout.session.completed':
                    $this->handleCheckoutSessionCompleted($event['data']['object']);
                    break;

                case 'payment_intent.succeeded':
                    $this->handlePaymentIntentSucceeded($event['data']['object']);
                    break;

                case 'payment_intent.payment_failed':
                    $this->handlePaymentIntentFailed($event['data']['object']);
                    break;

                default:
                    Log::info('ℹ️ Unhandled webhook event type: ' . $event['type']);
            }

            return response()->json(['status' => 'success']);

        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            Log::error('❌ Invalid webhook payload: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            Log::error('❌ Invalid webhook signature: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            Log::error('❌ Webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook error'], 500);
        }
    }

    /**
     * Handle successful checkout session
     */
    private function handleCheckoutSessionCompleted($session)
    {
        Log::info('✅ Checkout session completed: ' . $session['id']);

        // Get application ID from metadata
        $applicationId = $session['metadata']['application_id'] ?? null;

        if (!$applicationId) {
            Log::error('❌ No application_id in session metadata');
            return;
        }

        $application = PackageApplication::find($applicationId);

        if (!$application) {
            Log::error('❌ Application not found: ' . $applicationId);
            return;
        }

        // Update application status
        $application->update([
            'payment_status' => 'completed',
            'paid_at' => now(),
            'stripe_session_id' => $session['id'],
            'stripe_payment_intent_id' => $session['payment_intent'] ?? null,
        ]);

        Log::info("💰 Payment completed for application {$applicationId}");
        Log::info("📧 TODO: Send confirmation email to {$application->applicant_email}");
    }

    /**
     * Handle successful payment intent
     */
    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        Log::info('✅ Payment intent succeeded: ' . $paymentIntent['id']);

        // Find application by payment intent ID
        $application = PackageApplication::where('stripe_payment_intent_id', $paymentIntent['id'])->first();

        if ($application && $application->payment_status !== 'completed') {
            $application->update([
                'payment_status' => 'completed',
                'paid_at' => now(),
            ]);

            Log::info("💰 Payment completed for application {$application->id}");
        }
    }

    /**
     * Handle failed payment intent
     */
    private function handlePaymentIntentFailed($paymentIntent)
    {
        Log::info('❌ Payment intent failed: ' . $paymentIntent['id']);

        // Find application by payment intent ID
        $application = PackageApplication::where('stripe_payment_intent_id', $paymentIntent['id'])->first();

        if ($application) {
            $application->update([
                'payment_status' => 'failed',
            ]);

            Log::info("❌ Payment failed for application {$application->id}");
        }
    }
}
