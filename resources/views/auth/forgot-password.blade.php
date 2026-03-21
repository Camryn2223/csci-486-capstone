@extends('layouts.app')

@section('content')
    <h1>Reset Password</h1>

    <p>Enter your email address and we will send you a link to reset your password.</p>

    @if (session('status'))
        <p style="color:green">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <label>Email<br>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        </label>
        <br><br>

        <button type="submit">Send Reset Link</button>
    </form>

    <br><a href="{{ route('login') }}">Back to sign in</a>
@endsection