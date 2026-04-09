@extends('layouts.app')

@section('content')
    <div class="centered-content">
        <div class="form-box form-box-verify">
            <h2>Verify Your Email Address</h2>

            <p class="text-muted text-center mb-20">
                Thanks for signing up. Before getting started, please verify your
                email address by clicking the link we sent to your inbox.
            </p>

            @if (session('status') === 'verification-link-sent')
                <p class="text-success text-center">A new verification link has been sent to your email address.</p>
            @endif

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn">Resend Verification Email</button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="mt-15">
                @csrf
                <button type="submit" class="btn btn-outline w-full">Sign Out</button>
            </form>
        </div>
    </div>
@endsection