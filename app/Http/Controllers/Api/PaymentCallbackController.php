<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeePayment;
use App\Models\StudentFeeBillItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentCallbackController extends Controller
{
    /**
     * Handle payment webhook/callback notifications from external providers
     * 
     * POST /api/v1/payments/webhook/{provider}
     */
    public function handleWebhook(Request $request, $provider)
    {
        Log::info("Received payment webhook callback from provider: {$provider}");

        // 1. Verify Webhook Authenticity / Signature
        $signature = $request->header('X-Webhook-Signature');
        $secret = env('PAYMENT_WEBHOOK_SECRET');

        if ($secret) {
            if (!$signature) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing authorization signature.',
                ], 401);
            }

            $computedSignature = hash_hmac('sha256', $request->getContent(), $secret);
            if (!hash_equals($signature, $computedSignature)) {
                Log::warning("Unauthorized webhook access attempt from provider '{$provider}' (Signature mismatch).");
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid authorization signature.',
                ], 401);
            }
        }

        // 2. Parse payload based on provider format
        try {
            $data = $this->parsePayload($request, $provider);

            if (!$data || !$data['success']) {
                Log::warning("Payment webhook callback parsed as unsuccessful or invalid payload.", ['data' => $data]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unsuccessful transaction event ignored.',
                ], 200); // 200 OK because we received it, but no action needed
            }

            // 3. Process the payment transaction
            return DB::transaction(function () use ($data, $provider) {
                // Check if this reference number has already been recorded
                $existingPayment = FeePayment::where('reference_number', $data['reference_number'])->first();
                if ($existingPayment) {
                    Log::info("Payment webhook reference '{$data['reference_number']}' already recorded. Skipping duplication.");
                    return response()->json([
                        'success' => true,
                        'message' => 'Payment already processed.',
                    ], 200);
                }

                // Retrieve the targeted fee item and parent bill
                $billItem = StudentFeeBillItem::with('studentFeeBill')->find($data['student_fee_bill_item_id']);
                if (!$billItem || !$billItem->studentFeeBill) {
                    Log::error("Target bill item ID '{$data['student_fee_bill_item_id']}' not found from webhook callback.");
                    return response()->json([
                        'success' => false,
                        'message' => 'Associated fee bill item not found.',
                    ], 404);
                }

                $bill = $billItem->studentFeeBill;

                // Determine recorder system user (e.g. System user or first Administrator user)
                $systemUser = User::where('email', 'system@college.edu')
                    ->orWhereHas('roles', function($q) {
                        $q->whereIn('name', ['System', 'Super Admin']);
                    })
                    ->first();

                $recordedById = $systemUser ? $systemUser->id : 1;

                // Create the payment
                $payment = FeePayment::create([
                    'student_fee_bill_id' => $bill->id,
                    'student_fee_bill_item_id' => $billItem->id,
                    'student_id' => $bill->student_id,
                    'amount' => $data['amount'],
                    'payment_method' => $data['payment_method'] ?? 'Online Callback',
                    'reference_number' => $data['reference_number'],
                    'receipt_number' => 'FP' . date('Ymd') . strtoupper(Str::random(5)),
                    'external_receipt' => $data['external_receipt'],
                    'note' => $data['note'] ?? "Asynchronous Webhook Callback ({$provider})",
                    'recorded_by' => $recordedById,
                    'payment_date' => Carbon::now(),
                ]);

                // Recalculate bill payment status (this will also update the child items' statuses)
                $bill->recalculatePaymentStatus();

                Log::info("Processed webhook payment callback successfully.", [
                    'payment_id' => $payment->id,
                    'reference_number' => $payment->reference_number,
                    'receipt_number' => $payment->receipt_number,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Webhook callback processed successfully.',
                    'transaction_reference' => $payment->reference_number,
                    'receipt_number' => $payment->receipt_number,
                ], 200);
            });

        } catch (\Exception $e) {
            Log::error("Failed processing payment webhook callback.", [
                'error' => $e->getMessage(),
                'provider' => $provider,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error during webhook processing: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Parse the request payload based on the provider format
     */
    protected function parsePayload(Request $request, $provider)
    {
        $payload = $request->all();

        switch (strtolower($provider)) {
            case 'paystack':
                return [
                    'success' => isset($payload['event']) && $payload['event'] === 'charge.success' && isset($payload['data']['status']) && $payload['data']['status'] === 'success',
                    'reference_number' => $payload['data']['reference'] ?? null,
                    'amount' => isset($payload['data']['amount']) ? ($payload['data']['amount'] / 100) : 0, // Paystack is in cents/pesewas
                    'payment_method' => 'Paystack (' . ($payload['data']['channel'] ?? 'card') . ')',
                    'external_receipt' => $payload['data']['receipt_url'] ?? null,
                    'student_fee_bill_item_id' => $payload['data']['metadata']['student_fee_bill_item_id'] ?? $payload['data']['metadata']['custom_fields'][0]['value'] ?? null,
                    'note' => 'Paystack Webhook Payment Confirmation',
                ];

            case 'flutterwave':
                return [
                    'success' => isset($payload['event']) && $payload['event'] === 'charge.completed' && isset($payload['data']['status']) && $payload['data']['status'] === 'successful',
                    'reference_number' => $payload['data']['tx_ref'] ?? null,
                    'amount' => $payload['data']['amount'] ?? 0,
                    'payment_method' => 'Flutterwave (' . ($payload['data']['payment_type'] ?? 'card') . ')',
                    'external_receipt' => null,
                    'student_fee_bill_item_id' => $payload['data']['meta']['student_fee_bill_item_id'] ?? null,
                    'note' => 'Flutterwave Webhook Payment Confirmation',
                ];

            case 'generic':
            default:
                // Expected format for our standardized gateway integration
                return [
                    'success' => isset($payload['event']) && $payload['event'] === 'payment.success' && isset($payload['data']['status']) && $payload['data']['status'] === 'success',
                    'reference_number' => $payload['data']['reference'] ?? null,
                    'amount' => $payload['data']['amount'] ?? 0,
                    'payment_method' => $payload['data']['payment_method'] ?? 'Online Payment',
                    'external_receipt' => $payload['data']['external_receipt'] ?? null,
                    'student_fee_bill_item_id' => $payload['data']['metadata']['student_fee_bill_item_id'] ?? null,
                    'note' => $payload['data']['note'] ?? 'Generic Payment Webhook Confirmation',
                ];
        }
    }
}
