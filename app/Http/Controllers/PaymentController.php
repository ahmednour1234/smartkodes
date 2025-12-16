<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Plan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Show payment checkout page
     */
    public function checkout()
    {
        $registrationData = session('pending_registration');

        if (!$registrationData) {
            return redirect()->route('register')->with('error', 'No pending registration found.');
        }

        $tenant = Tenant::find($registrationData['tenant_id']);

        return view('payment.checkout', [
            'tenant' => $tenant,
            'amount' => $registrationData['amount'],
            'numUsers' => $registrationData['num_users'],
        ]);
    }

    /**
     * Process payment (placeholder - integrate with your payment gateway)
     */
    public function process(Request $request)
    {
        $registrationData = session('pending_registration');

        if (!$registrationData) {
            return redirect()->route('register')->with('error', 'No pending registration found.');
        }

        DB::beginTransaction();
        try {
            // TODO: Integrate with payment gateway (Stripe, PayPal, etc.)
            // For now, we'll simulate successful payment

            $tenant = Tenant::find($registrationData['tenant_id']);

            // Get or create a default plan
            $plan = Plan::firstOrCreate(
                ['slug' => 'standard'],
                [
                    'name' => 'Standard Plan',
                    'description' => 'Standard subscription plan - $10 per user per month',
                    'price' => 10.00,
                    'status' => 1,
                    'features' => json_encode([
                        'unlimited_projects' => true,
                        'dynamic_forms' => true,
                        'mobile_app' => true,
                        'offline_sync' => true,
                        'advanced_reports' => true,
                        'support_24_7' => true,
                    ]),
                ]
            );

            // Create subscription
            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => 1, // Active
                'start_date' => now(),
                'end_date' => now()->addMonth(),
                'created_by' => Auth::id(),
            ]);

            // Activate tenant after payment
            $tenant->update(['status' => 1]); // Active

            // Create payment record
            Payment::create([
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
                'amount' => $registrationData['amount'],
                'currency' => 'USD',
                'status' => 1, // 1 = Completed/Successful
                'gateway_response' => json_encode([
                    'payment_method' => $request->input('payment_method', 'bank_transfer'),
                    'transaction_id' => 'TXN-' . time() . '-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                    'processed_at' => now()->toIso8601String(),
                    'simulated' => true,
                ]),
            ]);

            DB::commit();

            // Clear session
            session()->forget('pending_registration');

            return redirect()->route('tenant.dashboard')
                ->with('success', 'Payment successful! Your account is now active.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Payment processing failed. Please try again. Error: ' . $e->getMessage());
        }
    }
}
