<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminPaymentController extends Controller
{
    public function verify(Payment $payment): RedirectResponse
    {
        try {
            $this->review($payment, 'Verified');
        } catch (ValidationException $exception) {
            return back()->with('error', $exception->errors()['payment'][0]);
        }

        return back()->with('status', 'Payment verified.');
    }

    public function reject(Payment $payment): RedirectResponse
    {
        try {
            $this->review($payment, 'Rejected');
        } catch (ValidationException $exception) {
            return back()->with('error', $exception->errors()['payment'][0]);
        }

        return back()->with('status', 'Payment rejected. The customer can upload a new proof.');
    }

    private function review(Payment $payment, string $result): void
    {
        DB::transaction(function () use ($payment, $result) {
            $locked = Payment::where('payment_id', $payment->payment_id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'Pending') {
                throw ValidationException::withMessages([
                    'payment' => 'This payment was already reviewed. Refresh and try again.',
                ]);
            }

            // Online payments need proof before they can be verified. Cash is
            // verified by staff once the money has actually been received.
            if ($result === 'Verified' && $locked->payment_method !== 'Cash' && ! $locked->proof_image) {
                throw ValidationException::withMessages([
                    'payment' => 'The customer has not uploaded proof of payment yet.',
                ]);
            }

            $locked->update([
                'status' => $result,
                'verified_by' => auth()->id(),
                'verified_at' => $result === 'Verified' ? now() : null,
            ]);
        });
    }
}
