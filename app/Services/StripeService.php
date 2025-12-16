<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Subscription;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Log;

class StripeService
{
    /**
     * Initialize Stripe with API key
     */
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret_key'));
    }

    /**
     * Create a Stripe customer
     */
    public function createCustomer(array $data): Customer
    {
        try {
            return Customer::create([
                'email' => $data['email'],
                'name' => $data['name'] ?? null,
                'phone' => $data['phone'] ?? null,
                'metadata' => $data['metadata'] ?? [],
            ]);
        } catch (ApiErrorException $e) {
            Log::error('Stripe customer creation failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Create a payment intent
     */
    public function createPaymentIntent(float $amount, string $currency = 'usd', array $metadata = []): PaymentIntent
    {
        try {
            return PaymentIntent::create([
                'amount' => (int) ($amount * 100), // Convert to cents
                'currency' => $currency,
                'metadata' => $metadata,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);
        } catch (ApiErrorException $e) {
            Log::error('Stripe payment intent creation failed', [
                'error' => $e->getMessage(),
                'amount' => $amount,
            ]);
            throw $e;
        }
    }

    /**
     * Create a checkout session
     */
    public function createCheckoutSession(array $data): Session
    {
        try {
            $sessionData = [
                'payment_method_types' => ['card'],
                'mode' => $data['mode'] ?? 'payment', // 'payment' or 'subscription'
                'success_url' => $data['success_url'],
                'cancel_url' => $data['cancel_url'],
                'metadata' => $data['metadata'] ?? [],
            ];

            if (isset($data['customer_id'])) {
                $sessionData['customer'] = $data['customer_id'];
            } else {
                $sessionData['customer_email'] = $data['customer_email'] ?? null;
            }

            if ($data['mode'] === 'subscription') {
                $sessionData['line_items'] = [
                    [
                        'price' => $data['price_id'],
                        'quantity' => $data['quantity'] ?? 1,
                    ],
                ];
            } else {
                $sessionData['line_items'] = [
                    [
                        'price_data' => [
                            'currency' => $data['currency'] ?? 'usd',
                            'product_data' => [
                                'name' => $data['product_name'],
                                'description' => $data['product_description'] ?? null,
                            ],
                            'unit_amount' => (int) ($data['amount'] * 100), // Convert to cents
                        ],
                        'quantity' => $data['quantity'] ?? 1,
                    ],
                ];
            }

            return Session::create($sessionData);
        } catch (ApiErrorException $e) {
            Log::error('Stripe checkout session creation failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Create a subscription
     */
    public function createSubscription(string $customerId, string $priceId, array $metadata = []): Subscription
    {
        try {
            return Subscription::create([
                'customer' => $customerId,
                'items' => [
                    ['price' => $priceId],
                ],
                'metadata' => $metadata,
            ]);
        } catch (ApiErrorException $e) {
            Log::error('Stripe subscription creation failed', [
                'error' => $e->getMessage(),
                'customer_id' => $customerId,
                'price_id' => $priceId,
            ]);
            throw $e;
        }
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(string $subscriptionId, bool $immediately = false): Subscription
    {
        try {
            $subscription = Subscription::retrieve($subscriptionId);
            
            if ($immediately) {
                return $subscription->cancel();
            } else {
                return $subscription->update([
                    'cancel_at_period_end' => true,
                ]);
            }
        } catch (ApiErrorException $e) {
            Log::error('Stripe subscription cancellation failed', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscriptionId,
            ]);
            throw $e;
        }
    }

    /**
     * Retrieve a payment intent
     */
    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        try {
            return PaymentIntent::retrieve($paymentIntentId);
        } catch (ApiErrorException $e) {
            Log::error('Stripe payment intent retrieval failed', [
                'error' => $e->getMessage(),
                'payment_intent_id' => $paymentIntentId,
            ]);
            throw $e;
        }
    }

    /**
     * Retrieve a checkout session
     */
    public function retrieveCheckoutSession(string $sessionId): Session
    {
        try {
            return Session::retrieve($sessionId);
        } catch (ApiErrorException $e) {
            Log::error('Stripe checkout session retrieval failed', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
            ]);
            throw $e;
        }
    }

    /**
     * Retrieve a customer
     */
    public function retrieveCustomer(string $customerId): Customer
    {
        try {
            return Customer::retrieve($customerId);
        } catch (ApiErrorException $e) {
            Log::error('Stripe customer retrieval failed', [
                'error' => $e->getMessage(),
                'customer_id' => $customerId,
            ]);
            throw $e;
        }
    }

    /**
     * Update a customer
     */
    public function updateCustomer(string $customerId, array $data): Customer
    {
        try {
            return Customer::update($customerId, $data);
        } catch (ApiErrorException $e) {
            Log::error('Stripe customer update failed', [
                'error' => $e->getMessage(),
                'customer_id' => $customerId,
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Create a payment method
     */
    public function attachPaymentMethod(string $customerId, string $paymentMethodId): Customer
    {
        try {
            $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);
            $paymentMethod->attach(['customer' => $customerId]);
            
            // Set as default payment method
            Customer::update($customerId, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethodId,
                ],
            ]);

            return $this->retrieveCustomer($customerId);
        } catch (ApiErrorException $e) {
            Log::error('Stripe payment method attachment failed', [
                'error' => $e->getMessage(),
                'customer_id' => $customerId,
                'payment_method_id' => $paymentMethodId,
            ]);
            throw $e;
        }
    }

    /**
     * List customer payment methods
     */
    public function listPaymentMethods(string $customerId): array
    {
        try {
            $paymentMethods = \Stripe\PaymentMethod::all([
                'customer' => $customerId,
                'type' => 'card',
            ]);

            return $paymentMethods->data;
        } catch (ApiErrorException $e) {
            Log::error('Stripe payment methods listing failed', [
                'error' => $e->getMessage(),
                'customer_id' => $customerId,
            ]);
            throw $e;
        }
    }

    /**
     * Handle webhook event
     */
    public function handleWebhook(string $payload, string $signature): object
    {
        try {
            $endpointSecret = config('services.stripe.webhook_secret');
            
            return \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                $endpointSecret
            );
        } catch (\Exception $e) {
            Log::error('Stripe webhook verification failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

