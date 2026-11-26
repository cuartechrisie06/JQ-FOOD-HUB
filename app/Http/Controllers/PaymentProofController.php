<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentProofController extends Controller
{
    /**
     * Customer submits (or re-submits) payment details for one of their orders.
     */
    public function store(Request $request, Order $order, Payment $payment): RedirectResponse
    {
        abort_unless(
            $order->user_id === auth()->id() && $payment->order_id === $order->order_id,
            404
        );

        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(['Cash', 'E-Wallet', 'Bank Transfer'])],
            'reference_no' => ['nullable', 'string', 'max:50'],
            'proof_image' => [
                Rule::requiredIf(fn () => $request->input('payment_method') !== 'Cash'
                    && (! $payment->proof_image || $payment->status === 'Rejected')),
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ], [
            'proof_image.required' => 'Please upload a screenshot or photo of your payment.',
            'proof_image.image' => 'The proof must be an image (JPG, PNG or WEBP).',
            'proof_image.max' => 'The proof image may not be larger than 4 MB.',
        ]);

        DB::transaction(function () use ($request, $order, $payment, $validated) {
            $payment = Payment::where('payment_id', $payment->payment_id)->lockForUpdate()->firstOrFail();

            if (! in_array($payment->status, ['Pending', 'Rejected'], true)) {
                throw ValidationException::withMessages([
                    'payment' => 'This payment has already been verified.',
                ]);
            }

            if (in_array($order->status, ['Cancelled', 'Completed'], true)) {
                throw ValidationException::withMessages([
                    'payment' => 'Payments can no longer be submitted for this order.',
                ]);
            }

            $path = $payment->proof_image;
            if ($request->hasFile('proof_image')) {
                $newPath = $request->file('proof_image')->store('payment-proofs', 'public');
                if ($path) {
                    Storage::disk('public')->delete($path);
                }
                $path = $newPath;
            }

            $payment->update([
                'payment_method' => $validated['payment_method'],
                'reference_no' => $validated['reference_no'] ?? null,
                'proof_image' => $path,
                'status' => 'Pending',
                'submitted_at' => now(),
                'verified_by' => null,
                'verified_at' => null,
            ]);
        });

        app(OrderNotificationService::class)->notifyAdminsOfPendingPayment($order);

        return back()->with('status', 'Payment details sent. We will review them shortly.');
    }
}
