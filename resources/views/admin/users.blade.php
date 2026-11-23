@extends('layouts.admin')
@section('title', 'Manage Users')
@section('content')
<h1 class="h3 mb-4">Manage Users</h1>

<div class="card mb-4"><div class="card-body">
<h2 class="h5">Create Admin Account</h2>
<form method="POST" action="{{ route('admin.users.store') }}" class="row g-3">
@csrf
<div class="col-md-6"><label class="form-label" for="admin_full_name">Full Name</label><input id="admin_full_name" class="form-control" name="full_name" value="{{ old('full_name') }}" required></div>
<div class="col-md-6"><label class="form-label" for="admin_email">Email</label><input id="admin_email" type="email" class="form-control" name="email" value="{{ old('email') }}" required></div>
<div class="col-md-4"><label class="form-label" for="admin_phone">Phone</label><input id="admin_phone" class="form-control" name="phone" value="{{ old('phone') }}" required></div>
<div class="col-md-4"><label class="form-label" for="admin_password">Password</label><input id="admin_password" type="password" class="form-control" name="password" required></div>
<div class="col-md-4"><label class="form-label" for="admin_password_confirmation">Confirm Password</label><input id="admin_password_confirmation" type="password" class="form-control" name="password_confirmation" required></div>
<div class="col-12"><button class="btn btn-jq" type="submit">Create Admin</button></div>
</form>
</div></div>

<div class="card"><div class="card-body table-responsive">
<table class="table align-middle">
<thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
@forelse($users as $user)
<tr>
<td>{{ $user->full_name }}</td><td>{{ $user->email }}</td>
<td>
@if($user->role->role_name === 'Owner')
<span class="badge text-bg-dark">Owner</span>
@else
<form method="POST" action="{{ route('admin.users.role', $user) }}" class="d-flex gap-2">
@csrf @method('PATCH')
<select class="form-select form-select-sm" name="role_id" aria-label="Role">
@foreach($roles as $role)<option value="{{ $role->role_id }}" @selected($user->role_id === $role->role_id)>{{ $role->role_name }}</option>@endforeach
</select>
<button class="btn btn-sm btn-outline-dark" type="submit">Save</button>
</form>
@endif
</td>
<td>{{ $user->status }}</td>
<td>
@if($user->role->role_name !== 'Owner')
<form method="POST" action="{{ route('admin.users.status', $user) }}">
@csrf @method('PATCH')
<input type="hidden" name="status" value="{{ $user->status === 'Active' ? 'Inactive' : 'Active' }}">
<button class="btn btn-sm {{ $user->status === 'Active' ? 'btn-outline-danger' : 'btn-outline-success' }}" type="submit">
{{ $user->status === 'Active' ? 'Deactivate' : 'Activate' }}
</button>
</form>
@endif
</td>
</tr>
@empty
<tr><td colspan="5" class="text-center text-muted py-4">No users found.</td></tr>
@endforelse
</tbody>
</table>
</div></div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
