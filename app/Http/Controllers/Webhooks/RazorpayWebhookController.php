<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RazorpayWebhookController extends Controller
{
    public function __invoke(Request $request, PaymentGatewayManager $manager): JsonResponse
    {
        $rawPayload = $request->getContent();
        $signature = $request->header('X-Webhook-Signature');

        if (! $manager->isSignatureValid('razorpay', $rawPayload, $signature)) {
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $normalized = $manager->for('razorpay')->handleWebhook($request->all(), $request->headers->all());

        Payment::query()->updateOrCreate(
            ['reference' => $normalized['reference']],
            [
                'admin_id' => (int) ($request->input('payload.payment.entity.notes.admin_id') ?? 1),
                'provider' => 'razorpay',
                'amount' => (float) ($normalized['amount'] ?? 0),
                'currency' => $normalized['currency'] ?? 'INR',
                'status' => $normalized['status'] ?? 'pending',
                'payload' => $normalized['payload'] ?? $request->all(),
            ]
        );

        return response()->json(['received' => true]);
    }
}
