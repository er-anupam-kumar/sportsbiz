<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGateway;
use InvalidArgumentException;

class PaymentGatewayManager
{
    public function for(string $provider): PaymentGateway
    {
        return match ($provider) {
            'stripe' => app(StripePaymentGateway::class),
            'razorpay' => app(RazorpayPaymentGateway::class),
            default => throw new InvalidArgumentException('Unsupported payment provider: '.$provider),
        };
    }

    public function isSignatureValid(string $provider, string $payload, ?string $signature): bool
    {
        $secret = (string) config("payments.providers.{$provider}.webhook_secret");

        if ($secret === '' || ! $signature) {
            return false;
        }

        $computed = hash_hmac('sha256', $payload, $secret);

        return hash_equals($computed, $signature);
    }
}
