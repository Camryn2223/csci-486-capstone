@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <h1 style="margin-top: 0;">Edit Organization</h1>

        <form method="POST" action="{{ route('organizations.update', $organization) }}">
            @csrf
            @method('PUT')
            
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name', $organization->name) }}" required>
            
            <div style="margin-top: 15px;">
                <button type="submit" class="btn">Save Changes</button>
                <a href="{{ route('organizations.show', $organization) }}" class="btn" style="background: #24282d; border: 1px solid #3a3f45; margin-left: 10px;">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
