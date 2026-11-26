@php
    $pay = config('jqfood.payments', []);
    $orderClosed = in_array($order->status, ['Cancelled', 'Completed'], true);
    $ref = 'JQ-'.str_pad($order->order_id, 4, '0', STR_PAD_LEFT);
@endphp

@foreach($order->payments as $payment)
    @php
        $canSubmit = ! $orderClosed && in_array($payment->status, ['Pending', 'Rejected'], true);
        $selectedMethod = old('payment_method', $payment->payment_method);
        $hasProof = (bool) $payment->proof_image;
    @endphp
    <div class="card mb-4" id="payment-{{ $payment->payment_id }}">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                <div>
                    <h2 class="h5 fw-bold mb-1">{{ $payment->payment_type }}</h2>
                    <div class="text-muted small">Amount to pay: <strong class="text-danger fs-6">₱{{ number_format($payment->amount, 2) }}</strong></div>
                </div>
                @include('partials.status-badge', ['status' => $payment->status])
            </div>

            @if($payment->status === 'Verified')
                <div class="alert alert-success mb-0"><i class="bi bi-check-circle-fill me-1" aria-hidden="true"></i> Payment received and verified. Thank you!</div>
            @else
                @if($payment->status === 'Rejected')
                    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-1" aria-hidden="true"></i> We could not verify this payment. Please check the details and send a clear proof again.</div>
                @elseif($hasProof)
                    <div class="alert alert-warning"><i class="bi bi-hourglass-split me-1" aria-hidden="true"></i> Proof received. We are reviewing your payment.</div>
                @elseif($payment->payment_method === 'Cash')
                    <div class="alert alert-info"><i class="bi bi-cash-coin me-1" aria-hidden="true"></i> Cash selected. Pay at our kitchen or on delivery and we will mark it as received. You can also switch to GCash or bank transfer below.</div>
                @endif

                @if($canSubmit)
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <h3 class="h6 fw-bold">How to pay</h3>
                            <div class="small">
                                <div class="mb-2" data-pay-info="E-Wallet" @if($selectedMethod !== 'E-Wallet') hidden @endif>
                                    <div class="fw-semibold">{{ $pay['ewallet_name'] ?? 'E-Wallet' }}</div>
                                    @if(! empty($pay['ewallet_number']))
                                        <div>Number: <strong class="user-select-all">{{ $pay['ewallet_number'] }}</strong></div>
                                        @if(! empty($pay['ewallet_account_name']))<div>Account name: <strong>{{ $pay['ewallet_account_name'] }}</strong></div>@endif
                                    @else
                                        <div class="text-muted">Payment details will be shared by the shop. Message us on Messenger.</div>
                                    @endif
                                </div>
                                <div class="mb-2" data-pay-info="Bank Transfer" @if($selectedMethod !== 'Bank Transfer') hidden @endif>
                                    <div class="fw-semibold">{{ $pay['bank_name'] ?? 'Bank Transfer' }}</div>
                                    @if(! empty($pay['bank_account_number']))
                                        <div>Account number: <strong class="user-select-all">{{ $pay['bank_account_number'] }}</strong></div>
                                        @if(! empty($pay['bank_account_name']))<div>Account name: <strong>{{ $pay['bank_account_name'] }}</strong></div>@endif
                                    @else
                                        <div class="text-muted">Payment details will be shared by the shop. Message us on Messenger.</div>
                                    @endif
                                </div>
                                <div class="mb-2" data-pay-info="Cash" @if($selectedMethod !== 'Cash') hidden @endif>
                                    <div class="text-muted">No upload needed. Hand the amount to our staff and we will mark it received.</div>
                                </div>
                                <div class="text-muted">Use <strong>{{ $ref }}</strong> as your payment reference.</div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <form method="POST" action="{{ route('payments.proof', [$order, $payment]) }}" enctype="multipart/form-data" data-once data-payment-form>
                                @csrf
                                @if($errors->hasAny(['payment_method', 'reference_no', 'proof_image', 'payment']))
                                    <div class="alert alert-danger py-2 small" role="alert">
                                        <ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                                    </div>
                                @endif
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="payment_method_{{ $payment->payment_id }}">Payment method</label>
                                        <select class="form-select" id="payment_method_{{ $payment->payment_id }}" name="payment_method" data-pay-method>
                                            @foreach(['Cash' => 'Cash', 'E-Wallet' => ($pay['ewallet_name'] ?? 'E-Wallet'), 'Bank Transfer' => 'Bank Transfer'] as $value => $label)
                                                <option value="{{ $value }}" @selected($selectedMethod === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6" data-pay-online @if($selectedMethod === 'Cash') hidden @endif>
                                        <label class="form-label" for="reference_no_{{ $payment->payment_id }}">Reference no. <span class="text-muted fw-normal">(optional)</span></label>
                                        <input class="form-control" id="reference_no_{{ $payment->payment_id }}" name="reference_no" maxlength="50" value="{{ old('reference_no', $payment->reference_no) }}" placeholder="e.g. 1234 567 890">
                                    </div>
                                    <div class="col-12" data-pay-online @if($selectedMethod === 'Cash') hidden @endif>
                                        <label class="form-label" for="proof_image_{{ $payment->payment_id }}">Proof of payment</label>
                                        <input class="form-control" type="file" id="proof_image_{{ $payment->payment_id }}" name="proof_image" accept="image/png,image/jpeg,image/webp">
                                        <div class="form-text">Screenshot or photo of your receipt (JPG, PNG or WEBP, up to 4 MB).</div>
                                        @if($hasProof)
                                            <a class="small d-inline-block mt-2" href="{{ asset('storage/'.$payment->proof_image) }}" target="_blank" rel="noopener"><i class="bi bi-image" aria-hidden="true"></i> View current proof</a>
                                        @endif
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-jq" type="submit"><i class="bi bi-upload" aria-hidden="true"></i> {{ $payment->status === 'Rejected' ? 'Send new proof' : ($hasProof ? 'Update payment details' : 'Submit payment') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
@endforeach
