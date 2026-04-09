@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <h1 class="mt-0">Create Organization</h1>

        <form method="POST" action="{{ route('organizations.store') }}">
            @csrf
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
            
            <div class="mt-15">
                <button type="submit" class="btn">Create Organization</button>
                <a href="{{ route('organizations.index') }}" class="btn btn-outline ml-10">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection