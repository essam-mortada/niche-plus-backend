<?php

namespace App\Http\Controllers;

use App\Models\Award;
use App\Models\PackageApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    // Package prices
    private const PACKAGES = [
        'nomination' => 1500.00,
        'majesty' => 35000.00,
        'sovereign' => 75000.00,
        'monarch' => 120000.00,
    ];

    /**
     * Get available packages for an award
     */
    public function getPackages($awardId)
    {
        $award = Award::findOrFail($awardId);

        // Get packages from database that are available
        $packages = \App\Models\EventPackage::where('award_id', $awardId)
            ->where('is_available', true)
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'type' => $package->package_type,
                    'name' => ucfirst($package->package_type),
                    'price' => (float) $package->price,
                    'description' => $package->description,
                    'benefits' => $package->benefits ?? [],
                ];
            });

        return response()->json([
            'award' => $award,
            'packages' => $packages,
        ]);
    }

    /**
     * Create application and payment session
     */
    public function createApplication(Request $request)
    {
        $validated = $request->validate([
            'award_id' => 'required|exists:awards,id',
            'package_type' => 'required|in:nomination,majesty,sovereign,monarch',
            'applicant_name' => 'required|string|max:255',
            'applicant_email' => 'required|email',
            'applicant_phone' => 'required|string|max:50',
            'company_name' => 'required|string|max:255',
            'company_size' => 'required|string|max:100',
            'evidence_links' => 'nullable|string',
        ]);

        // Get package from database to get the price
        $package = \App\Models\EventPackage::where('award_id', $validated['award_id'])
            ->where('package_type', $validated['package_type'])
            ->where('is_available', true)
            ->first();

        if (!$package) {
            return response()->json([
                'message' => 'Package not available for this award'
            ], 404);
        }

        $amount = $package->price;

        // Create application
        $application = PackageApplication::create([
            'user_id' => auth()->id(),
            'award_id' => $validated['award_id'],
            'package_type' => $validated['package_type'],
            'amount' => $amount,
            'applicant_name' => $validated['applicant_name'],
            'applicant_email' => $validated['applicant_email'],
            'applicant_phone' => $validated['applicant_phone'],
            'company_name' => $validated['company_name'],
            'company_size' => $validated['company_size'],
            'evidence_links' => $validated['evidence_links'],
            'payment_status' => 'pending',
        ]);

        // Create Stripe payment URL
        // In production, you would create actual Stripe session here
        // For now, we'll create a mock payment URL
        $paymentUrl = $this->createStripeSession($application);

        return response()->json([
            'application' => $application,
            'payment_url' => $paymentUrl,
            'message' => 'Application created successfully',
        ], 201);
    }

    /**
     * Create Stripe checkout session
     */
    private function createStripeSession($application)
    {
        try {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

            $packageNames = [
                'nomination' => 'Nomination Entry',
                'majesty' => 'Majesty Sponsor',
                'sovereign' => 'Sovereign Sponsor',
                'monarch' => 'Monarch Sponsor',
            ];

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $packageNames[$application->package_type],
                            'description' => "Award: {$application->award->title}",
                        ],
                        'unit_amount' => $application->amount * 100, // Convert to cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => "nicheplus://payment/success?session_id={CHECKOUT_SESSION_ID}&application_id={$application->id}",
                'cancel_url' => "nicheplus://payment/failed?application_id={$application->id}",
                'metadata' => [
                    'application_id' => $application->id,
                    'user_id' => $application->user_id,
                ],
            ]);

            // Save session ID
            $application->update(['stripe_session_id' => $session->id]);

            return $session->url;
        } catch (\Exception $e) {
            Log::error('Stripe session creation failed: ' . $e->getMessage());
            
            // Fallback to mock URL for development
            $baseUrl = env('APP_URL', 'http://127.0.0.1:8000');
            return "{$baseUrl}/api/payment/mock-checkout/{$application->id}";
        }
    }

    /**
     * Handle payment success callback
     * Note: This is public (no auth) because it's called from browser/Stripe
     */
    public function paymentSuccess(Request $request, $applicationId)
    {
        Log::info("💰 Payment success callback received for application: {$applicationId}");
        
        $application = PackageApplication::with('award')->findOrFail($applicationId);
        
        Log::info("📋 Current status: {$application->payment_status}");

        // Verify Stripe session if session_id provided
        if ($request->has('session_id')) {
            try {
                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
                $session = \Stripe\Checkout\Session::retrieve($request->input('session_id'));
                
                if ($session->payment_status === 'paid') {
                    $application->update([
                        'payment_status' => 'completed',
                        'paid_at' => now(),
                        'stripe_payment_intent_id' => $session->payment_intent,
                    ]);
                    Log::info("✅ Stripe payment verified and status updated");
                    
                    // Send confirmation email
                    try {
                        \Mail::to($application->applicant_email)->send(new \App\Mail\PaymentConfirmation($application));
                        Log::info("📧 Confirmation email sent to: {$application->applicant_email}");
                    } catch (\Exception $e) {
                        Log::error("❌ Failed to send email: " . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                Log::error('❌ Stripe verification failed: ' . $e->getMessage());
            }
        } else {
            // Mock payment for development
            Log::info("🧪 Mock payment - updating status to completed");
            $application->update([
                'payment_status' => 'completed',
                'paid_at' => now(),
                'stripe_payment_intent_id' => 'mock_' . uniqid(),
            ]);
            Log::info("✅ Status updated to: {$application->fresh()->payment_status}");
            
            // Send confirmation email
            try {
                \Mail::to($application->applicant_email)->send(new \App\Mail\PaymentConfirmation($application));
                Log::info("📧 Confirmation email sent to: {$application->applicant_email}");
            } catch (\Exception $e) {
                Log::error("❌ Failed to send email: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment completed successfully!',
            'application' => $application->fresh(),
        ]);
    }

    /**
     * Handle payment failure callback
     * Note: This is public (no auth) because it's called from browser/Stripe
     */
    public function paymentFailed(Request $request, $applicationId)
    {
        Log::info("❌ Payment failed callback received for application: {$applicationId}");
        
        $application = PackageApplication::findOrFail($applicationId);

        $application->update([
            'payment_status' => 'failed',
        ]);
        
        Log::info("✅ Status updated to: failed");

        return response()->json([
            'success' => false,
            'message' => 'Payment failed. Please try again.',
            'application' => $application->fresh(),
        ], 400);
    }

    /**
     * Get user's applications
     */
    public function myApplications()
    {
        $applications = PackageApplication::where('user_id', auth()->id())
            ->with(['award'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($applications);
    }

    /**
     * Get all applications (Admin only)
     */
    public function getAllApplications()
    {
        $applications = PackageApplication::with(['award', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($applications);
    }

    /**
     * Get application details and check Stripe status
     */
    public function getApplication($id)
    {
        $application = PackageApplication::with(['award', 'user'])
            ->findOrFail($id);

        // Check if user owns this application or is admin
        if ($application->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // If payment is pending and we have a Stripe session, check its status
        if ($application->payment_status === 'pending' && $application->stripe_session_id) {
            try {
                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
                $session = \Stripe\Checkout\Session::retrieve($application->stripe_session_id);
                
                Log::info("🔍 Checking Stripe session: {$session->id}");
                Log::info("💳 Payment status: {$session->payment_status}");
                
                // Update status based on Stripe session
                if ($session->payment_status === 'paid') {
                    $application->update([
                        'payment_status' => 'completed',
                        'paid_at' => now(),
                        'stripe_payment_intent_id' => $session->payment_intent,
                    ]);
                    Log::info("✅ Updated application {$id} to completed");
                    
                    // Send confirmation email
                    try {
                        \Mail::to($application->applicant_email)->send(new \App\Mail\PaymentConfirmation($application));
                        Log::info("📧 Confirmation email sent to: {$application->applicant_email}");
                    } catch (\Exception $e) {
                        Log::error("❌ Failed to send email: " . $e->getMessage());
                    }
                } elseif ($session->payment_status === 'unpaid') {
                    // Check if session is expired
                    if ($session->status === 'expired') {
                        $application->update(['payment_status' => 'failed']);
                        Log::info("❌ Session expired, marked as failed");
                    }
                }
                
                // Refresh the application data
                $application->refresh();
            } catch (\Exception $e) {
                Log::error("❌ Error checking Stripe session: " . $e->getMessage());
            }
        }

        return response()->json($application);
    }

    /**
     * Mock checkout page for testing (without Stripe)
     */
    public function mockCheckout($applicationId)
    {
        $application = PackageApplication::with('award')->findOrFail($applicationId);
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Mock Payment - Niche Plus</title>
            <meta name='viewport' content='width=device-width, initial-scale=1'>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
                    color: #fff;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                .container {
                    background: rgba(255, 255, 255, 0.05);
                    backdrop-filter: blur(10px);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    border-radius: 20px;
                    padding: 40px;
                    max-width: 500px;
                    width: 100%;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
                }
                h1 { color: #D4AF37; margin-bottom: 10px; font-size: 28px; }
                .subtitle { color: #999; margin-bottom: 30px; }
                .detail { 
                    display: flex; 
                    justify-content: space-between; 
                    padding: 15px 0; 
                    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                }
                .label { color: #999; }
                .value { font-weight: 600; }
                .amount { color: #D4AF37; font-size: 32px; font-weight: 700; margin: 30px 0; text-align: center; }
                .button {
                    width: 100%;
                    padding: 18px;
                    border: none;
                    border-radius: 12px;
                    font-size: 16px;
                    font-weight: 700;
                    cursor: pointer;
                    margin-top: 10px;
                    transition: all 0.3s;
                }
                .success { background: #D4AF37; color: #000; }
                .success:hover { background: #c4a137; transform: translateY(-2px); }
                .fail { background: transparent; border: 2px solid #F44336; color: #F44336; }
                .fail:hover { background: rgba(244, 67, 54, 0.1); }
                .note { 
                    text-align: center; 
                    color: #666; 
                    font-size: 12px; 
                    margin-top: 20px;
                    padding-top: 20px;
                    border-top: 1px solid rgba(255, 255, 255, 0.1);
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>🔒 Secure Payment</h1>
                <p class='subtitle'>Mock Payment Gateway (Testing Mode)</p>
                
                <div class='detail'>
                    <span class='label'>Package</span>
                    <span class='value'>" . ucfirst($application->package_type) . "</span>
                </div>
                <div class='detail'>
                    <span class='label'>Award</span>
                    <span class='value'>{$application->award->city}</span>
                </div>
                <div class='detail'>
                    <span class='label'>Applicant</span>
                    <span class='value'>{$application->applicant_name}</span>
                </div>
                
                <div class='amount'>\${$application->amount}</div>
                
                <button class='button success' onclick='completePayment()'>
                    ✓ Complete Payment
                </button>
                <button class='button fail' onclick='failPayment()'>
                    ✕ Simulate Payment Failure
                </button>
                
                <p class='note'>
                    This is a mock payment page for testing. In production, this would be Stripe's secure checkout.
                </p>
            </div>
            
            <script>
                function completePayment() {
                    const button = event.target;
                    button.disabled = true;
                    button.textContent = 'Processing...';
                    
                    // Get the full URL
                    const baseUrl = window.location.origin;
                    const apiUrl = baseUrl + '/api/payment/success/{$applicationId}';
                    
                    console.log('Calling API:', apiUrl);
                    
                    // Update payment status
                    fetch(apiUrl, {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        // Show success message
                        document.querySelector('.container').innerHTML = `
                            <div style='text-align: center;'>
                                <div style='font-size: 64px; margin-bottom: 20px;'>✅</div>
                                <h1 style='color: #4CAF50; margin-bottom: 20px;'>Payment Successful!</h1>
                                <p style='color: #999; font-size: 18px; margin-bottom: 30px;'>
                                    Your payment has been processed successfully.
                                </p>
                                <div style='background: rgba(76, 175, 80, 0.1); border: 2px solid #4CAF50; border-radius: 12px; padding: 20px; margin-bottom: 20px;'>
                                    <p style='color: #fff; font-size: 16px; margin: 0;'>
                                        ✨ Please return to the Niche Plus app to continue
                                    </p>
                                </div>
                                <p style='color: #666; font-size: 14px;'>
                                    The app will automatically detect your payment and show the confirmation screen.
                                </p>
                            </div>
                        `;
                        
                        // Try to redirect to app (may not work in all browsers)
                        setTimeout(() => {
                            window.location.href = 'nicheplus://payment/success?application_id={$applicationId}';
                        }, 500);
                    }).catch(error => {
                        button.disabled = false;
                        button.textContent = '✓ Complete Payment';
                        alert('Error processing payment. Please try again.');
                    });
                }
                
                function failPayment() {
                    const button = event.target;
                    button.disabled = true;
                    button.textContent = 'Processing...';
                    
                    // Get the full URL
                    const baseUrl = window.location.origin;
                    const apiUrl = baseUrl + '/api/payment/failed/{$applicationId}';
                    
                    console.log('Calling API:', apiUrl);
                    
                    // Update payment status
                    fetch(apiUrl, {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        // Show failure message
                        document.querySelector('.container').innerHTML = `
                            <div style='text-align: center;'>
                                <div style='font-size: 64px; margin-bottom: 20px;'>❌</div>
                                <h1 style='color: #F44336; margin-bottom: 20px;'>Payment Failed</h1>
                                <p style='color: #999; font-size: 18px; margin-bottom: 30px;'>
                                    The payment could not be processed.
                                </p>
                                <div style='background: rgba(244, 67, 54, 0.1); border: 2px solid #F44336; border-radius: 12px; padding: 20px; margin-bottom: 20px;'>
                                    <p style='color: #fff; font-size: 16px; margin: 0;'>
                                        ⚠️ Please return to the Niche Plus app to try again
                                    </p>
                                </div>
                                <p style='color: #666; font-size: 14px;'>
                                    The app will automatically detect the failure and allow you to retry.
                                </p>
                            </div>
                        `;
                        
                        // Try to redirect to app (may not work in all browsers)
                        setTimeout(() => {
                            window.location.href = 'nicheplus://payment/failed?application_id={$applicationId}';
                        }, 500);
                    }).catch(error => {
                        button.disabled = false;
                        button.textContent = '✕ Simulate Payment Failure';
                        alert('Error processing request. Please try again.');
                    });
                }
            </script>
        </body>
        </html>
        ";
        
        return response($html)->header('Content-Type', 'text/html');
    }
}
