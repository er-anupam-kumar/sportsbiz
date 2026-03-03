<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGateway;

class StripePaymentGateway implements PaymentGateway
{
    public function createCheckout(array $payload): array
    {
        return [
            'provider' => 'stripe',
            'checkout_url' => '',
            'reference' => $payload['reference'] ?? ('stripe_'.uniqid()),
            'status' => 'pending',
            'meta' => $payload,
        ];
    }

    public function handleWebhook(array $payload, array $headers = []): array
    {
        return [
            'provider' => 'stripe',
            'reference' => $payload['data']['object']['id'] ?? ($payload['id'] ?? 'stripe_unknown'),
            'status' => ($payload['type'] ?? '') === 'checkout.session.completed' ? 'succeeded' : 'pending',
            'amount' => (($payload['data']['object']['amount_total'] ?? 0) / 100),
            'currency' => strtoupper((string) ($payload['data']['object']['currency'] ?? 'INR')),
            'payload' => $payload,
        ];
    }
}
