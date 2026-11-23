<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(): View
    {
        $orders = auth()->user()->orders()
            ->where('status', 'Completed')
            ->with('review')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('reviews.mine', compact('orders'));
    }

    public function store(StoreReviewRequest $request, Order $order): RedirectResponse
    {
        $order->review()->create([
            'user_id' => $request->user()->user_id,
            ...$request->validated(),
            'status' => 'Pending',
            'created_at' => now(),
        ]);

        return redirect()->route('reviews.mine')->with('status', 'Your review was submitted for approval.');
    }

    public function update(UpdateReviewRequest $request, Review $review): RedirectResponse
    {
        $review->update($request->validated());

        return redirect()->route('reviews.mine')->with('status', 'Your pending review was updated.');
    }
}
