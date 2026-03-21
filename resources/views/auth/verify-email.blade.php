@extends('layouts.app')

@section('content')
    <h1>Verify Your Email Address</h1>

    <p>
        Thanks for signing up. Before getting started, please verify your
        email address by clicking the link we sent to your inbox.
    </p>

    @if (session('status') === 'verification-link-sent')
        <p style="color:green">A new verification link has been sent to your email address.</p>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit">Resend Verification Email</button>
    </form>

    <br>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Sign Out</button>
    </form>
@endsection