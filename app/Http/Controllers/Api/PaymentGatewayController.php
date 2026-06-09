<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\StudentFeeBill;
use App\Models\StudentFeeBillItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentGatewayController extends Controller
{
    /**
     * Get student details and active bills
     * 
     * GET /api/payment-gateway/student
     */
    public function getStudentDetails(Request $request)
    {
        $user = Auth::user();
        $student = null;

        // 1. Determine target student
        if ($user->hasRole('Student')) {
            $student = $user->student;
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authenticated user has Student role but no associated student profile.',
                ], 404);
            }
        } else {
            // Check authorization for external providers / admins
            $hasAccess = $user->hasRole(['System', 'Super Admin', 'Finance Manager', 'Administrator']) 
                || $user->hasAnyPermission(['view finance', 'view students']);

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to student payment details.',
                ], 403);
            }

            $request->validate([
                'student_id' => 'nullable|string',
                'student_reference' => 'nullable|string',
            ]);

            $studentId = $request->query('student_id') ?? $request->query('student_reference');

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide a student_id or student_reference parameter.',
                ], 400);
            }

            // Search by student registration number (student_id) first, then by primary key ID
            $student = Student::where('student_id', $studentId)
                ->orWhere('id', $studentId)
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found.',
                ], 404);
            }
        }

        // 2. Fetch bills with items and fee types
        $bills = StudentFeeBill::where('student_id', $student->id)
            ->with(['billItems.feeType', 'academicYear', 'semester'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'name' => $student->full_name,
                'email' => $student->email,
                'mobile_number' => $student->mobile_number,
                'class' => $student->collegeClass?->name ?? 'N/A',
                'cohort' => $student->cohort?->name ?? 'N/A',
                'status' => $student->status,
            ],
            'bills' => $bills->map(function ($bill) {
                return [
                    'id' => $bill->id,
                    'bill_reference' => $bill->bill_reference,
                    'academic_year' => $bill->academicYear?->name ?? 'N/A',
                    'semester' => $bill->semester?->name ?? 'N/A',
                    'total_amount' => (float)$bill->total_amount,
                    'amount_paid' => (float)$bill->amount_paid,
                    'balance' => (float)$bill->balance,
                    'payment_percentage' => (float)$bill->payment_percentage,
                    'status' => $bill->status,
                    'billing_date' => $bill->billing_date->toIso8601String(),
                    'items' => $bill->billItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'fee_name' => $item->feeType?->name ?? 'N/A',
                            'description' => $item->feeType?->description ?? '',
                            'amount' => (float)$item->amount,
                            'amount_paid' => (float)$item->amount_paid,
                            'balance' => (float)$item->balance,
                            'status' => $item->status,
                        ];
                    }),
                ];
            }),
        ]);
    }

    /**
     * Get details of a specific bill
     * 
     * GET /api/payment-gateway/bills/{id}
     */
    public function getBillDetails(Request $request, $id)
    {
        $user = Auth::user();
        $bill = StudentFeeBill::with(['billItems.feeType', 'student', 'academicYear', 'semester', 'payments'])->find($id);

        if (!$bill) {
            return response()->json([
                'success' => false,
                'message' => 'Bill not found.',
            ], 404);
        }

        // Authorize access
        if ($user->hasRole('Student')) {
            $student = $user->student;
            if (!$student || $bill->student_id !== $student->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this bill details.',
                ], 403);
            }
        } else {
            $hasAccess = $user->hasRole(['System', 'Super Admin', 'Finance Manager', 'Administrator']) 
                || $user->hasAnyPermission(['view finance']);

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to bill details.',
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'bill' => [
                'id' => $bill->id,
                'bill_reference' => $bill->bill_reference,
                'student' => [
                    'id' => $bill->student->id,
                    'student_id' => $bill->student->student_id,
                    'name' => $bill->student->full_name,
                ],
                'academic_year' => $bill->academicYear?->name ?? 'N/A',
                'semester' => $bill->semester?->name ?? 'N/A',
                'total_amount' => (float)$bill->total_amount,
                'amount_paid' => (float)$bill->amount_paid,
                'balance' => (float)$bill->balance,
                'payment_percentage' => (float)$bill->payment_percentage,
                'status' => $bill->status,
                'billing_date' => $bill->billing_date->toIso8601String(),
                'items' => $bill->billItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'fee_name' => $item->feeType?->name ?? 'N/A',
                        'amount' => (float)$item->amount,
                        'amount_paid' => (float)$item->amount_paid,
                        'balance' => (float)$item->balance,
                        'status' => $item->status,
                    ];
                }),
                'payments' => $bill->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => (float)$payment->amount,
                        'payment_method' => $payment->payment_method,
                        'reference_number' => $payment->reference_number,
                        'receipt_number' => $payment->receipt_number,
                        'external_receipt' => $payment->external_receipt,
                        'payment_date' => $payment->payment_date->toIso8601String(),
                        'reversed' => $payment->isReversed(),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Record a payment for a selected fee item
     * 
     * POST /api/payment-gateway/pay-item
     */
    public function recordItemPayment(Request $request)
    {
        $user = Auth::user();

        // 1. Authorize: external payment gateway or finance admins
        $hasAccess = $user->hasRole(['System', 'Super Admin', 'Finance Manager']) 
            || $user->hasAnyPermission(['process payments']);

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to record payments.',
            ], 403);
        }

        // 2. Validate request
        $request->validate([
            'student_fee_bill_item_id' => 'required|integer|exists:student_fee_bill_items,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'reference_number' => 'required|string|max:100|unique:fee_payments,reference_number',
            'external_receipt' => 'nullable|string',
            'note' => 'nullable|string|max:255',
        ]);

        try {
            return DB::transaction(function () use ($request, $user) {
                // Fetch the targeted fee item and parent bill
                $billItem = StudentFeeBillItem::with('studentFeeBill')->findOrFail($request->student_fee_bill_item_id);
                $bill = $billItem->studentFeeBill;

                if (!$bill) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Associated bill not found for the selected fee item.',
                    ], 404);
                }

                // Generate system receipt number
                $receiptNumber = 'FP' . date('Ymd') . strtoupper(Str::random(5));

                // Create the payment record
                $payment = FeePayment::create([
                    'student_fee_bill_id' => $bill->id,
                    'student_fee_bill_item_id' => $billItem->id,
                    'student_id' => $bill->student_id,
                    'amount' => $request->amount,
                    'payment_method' => $request->payment_method,
                    'reference_number' => $request->reference_number,
                    'receipt_number' => $receiptNumber,
                    'external_receipt' => $request->external_receipt,
                    'note' => $request->note ?? 'API Payment Gateway Recording',
                    'recorded_by' => $user->id,
                    'payment_date' => Carbon::now(),
                ]);

                // Recalculate bill payment status (this will also update the child items' statuses)
                $bill->recalculatePaymentStatus();

                // Refresh model instances to get updated balances
                $billItem->refresh();
                $bill->refresh();

                Log::info("Recorded API payment for student item ID {$billItem->id}", [
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                    'reference_number' => $payment->reference_number,
                    'receipt_number' => $payment->receipt_number,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment recorded successfully.',
                    'payment' => [
                        'id' => $payment->id,
                        'amount' => (float)$payment->amount,
                        'payment_method' => $payment->payment_method,
                        'reference_number' => $payment->reference_number,
                        'receipt_number' => $payment->receipt_number,
                        'external_receipt' => $payment->external_receipt,
                        'payment_date' => $payment->payment_date->toIso8601String(),
                    ],
                    'fee_item' => [
                        'id' => $billItem->id,
                        'amount' => (float)$billItem->amount,
                        'amount_paid' => (float)$billItem->amount_paid,
                        'balance' => (float)$billItem->balance,
                        'status' => $billItem->status,
                    ],
                    'bill' => [
                        'id' => $bill->id,
                        'bill_reference' => $bill->bill_reference,
                        'total_amount' => (float)$bill->total_amount,
                        'amount_paid' => (float)$bill->amount_paid,
                        'balance' => (float)$bill->balance,
                        'status' => $bill->status,
                    ]
                ], 201);
            });
        } catch (\Exception $e) {
            Log::error('Failed to record API payment gateway transaction', [
                'error' => $e->getMessage(),
                'item_id' => $request->student_fee_bill_item_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to record payment due to system error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
