@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <h1 class="mt-0">Create Organization</h1>

        <form method="POST" action="{{ route('organizations.store') }}">
            @csrf
            
            @include('organizations.partials.form-fields', ['organization' => null])
            
            <div class="mt-15">
                <button type="submit" class="btn">Create Organization</button>
                <a href="{{ route('dashboard') }}" class="btn btn-outline ml-10">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection