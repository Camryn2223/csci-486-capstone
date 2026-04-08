@extends('layouts.app')

@section('content')
    <div class="centered-content">
        <div class="form-box" style="width: 500px;">
            <h2>Verify Your Email Address</h2>

            <p style="color: #bdbdbd; text-align: center; margin-bottom: 20px;">
                Thanks for signing up. Before getting started, please verify your
                email address by clicking the link we sent to your inbox.
            </p>

            @if (session('status') === 'verification-link-sent')
                <p style="color: #9dffb0; text-align: center;">A new verification link has been sent to your email address.</p>
            @endif

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn">Resend Verification Email</button>
            </form>

            <form method="POST" action="{{ route('logout') }}" style="margin-top: 15px;">
                @csrf
                <button type="submit" class="btn" style="background: #24282d; border: 1px solid #3a3f45; width: 100%;">Sign Out</button>
            </form>
        </div>
    </div>
@endsection
