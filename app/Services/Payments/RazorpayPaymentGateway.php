<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGateway;

class RazorpayPaymentGateway implements PaymentGateway
{
    public function createCheckout(array $payload): array
    {
        return [
            'provider' => 'razorpay',
            'checkout_url' => '',
            'reference' => $payload['reference'] ?? ('rzp_'.uniqid()),
            'status' => 'pending',
            'meta' => $payload,
        ];
    }

    public function handleWebhook(array $payload, array $headers = []): array
    {
        return [
            'provider' => 'razorpay',
            'reference' => $payload['payload']['payment']['entity']['id'] ?? 'razorpay_unknown',
            'status' => ($payload['event'] ?? '') === 'payment.captured' ? 'succeeded' : 'pending',
            'amount' => (($payload['payload']['payment']['entity']['amount'] ?? 0) / 100),
            'currency' => strtoupper((string) ($payload['payload']['payment']['entity']['currency'] ?? 'INR')),
            'payload' => $payload,
        ];
    }
}
