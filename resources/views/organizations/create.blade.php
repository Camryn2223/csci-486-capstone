@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <h1 style="margin-top: 0;">Create Organization</h1>

        <form method="POST" action="{{ route('organizations.store') }}">
            @csrf
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
            
            <div style="margin-top: 15px;">
                <button type="submit" class="btn">Create Organization</button>
                <a href="{{ route('organizations.index') }}" class="btn" style="background: #24282d; border: 1px solid #3a3f45; margin-left: 10px;">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection