<?php

namespace App\Http\Controllers;

use App\Http\Requests\AvailabilityCalendarRequest;
use App\Models\AvailabilityCalendar;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminAvailabilityCalendarController extends Controller
{
    public function index(): View
    {
        return view('admin.availability.index', [
            'calendars' => AvailabilityCalendar::withCount([
                'bookings as confirmed_bookings_count' => fn ($query) => $query->where('status', 'Confirmed'),
            ])->orderByDesc('slot_date')->limit(100)->get(),
        ]);
    }

    public function store(AvailabilityCalendarRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $calendar = AvailabilityCalendar::getOrCreateForDate($validated['slot_date']);
        $calendar->update([
            'max_bookings' => $validated['max_bookings'],
            'is_blocked' => $request->boolean('is_blocked'),
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return redirect()->route('admin.availability.index')->with('status', 'Calendar availability updated.');
    }
}
