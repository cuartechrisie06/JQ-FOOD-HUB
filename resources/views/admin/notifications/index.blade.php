@extends('layouts.admin')
@section('title', 'Notifications')
@section('content')
<h1 class="h3 mb-3">Notifications</h1>
<div class="card"><div class="list-group list-group-flush">
    @forelse($notifications as $notification)
        <div class="list-group-item {{ $notification->is_read ? '' : 'list-group-item-warning' }} d-flex justify-content-between gap-3">
            <div>
                <strong>{{ $notification->type }}</strong>
                <div>{{ $notification->message }}</div>
                <small class="text-muted">{{ $notification->created_at->format('M j, Y g:i A') }}</small>
            </div>
            @if(! $notification->is_read)
                <form method="POST" action="{{ route('admin.notifications.read', $notification) }}">
                    @csrf @method('PATCH')
                    <button class="btn btn-sm btn-outline-dark">Mark read</button>
                </form>
            @endif
        </div>
    @empty
        <div class="list-group-item text-muted text-center py-4">No notifications.</div>
    @endforelse
</div></div>
<div class="mt-3">{{ $notifications->links() }}</div>
@endsection
