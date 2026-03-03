<?php

namespace App\Contracts\Payments;

interface PaymentGateway
{
    public function createCheckout(array $payload): array;

    public function handleWebhook(array $payload, array $headers = []): array;
}
