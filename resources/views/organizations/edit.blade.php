@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <h1 class="mt-0">Edit Organization</h1>

        <form method="POST" action="{{ route('organizations.update', $organization) }}">
            @csrf
            @method('PUT')
            
            @include('organizations.partials.form-fields', ['organization' => $organization])
            
            <div class="mt-15">
                <button type="submit" class="btn">Save Changes</button>
                <a href="{{ route('organizations.show', $organization) }}" class="btn btn-outline ml-10">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection