<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminReviewController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:Pending,Approved,Hidden'],
        ]);
        $reviews = Review::with(['user', 'order.calendar'])
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function updateStatus(Request $request, Review $review): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:Approved,Hidden'],
        ]);
        $review->update(['status' => $validated['status']]);

        return back()->with('status', 'Review status updated.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('status', 'Review deleted.');
    }
}
