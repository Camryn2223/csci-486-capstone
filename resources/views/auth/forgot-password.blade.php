@extends('layouts.app')

@section('content')
    <div class="centered-content">
        <div class="form-box">
            <h2>Reset Password</h2>

            <p style="color: #bdbdbd; text-align: center; font-size: 14px;">Enter your email address and we will send you a link to reset your password.</p>

            @if (session('status'))
                <p style="color: #9dffb0; text-align: center;">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">

                <button type="submit" class="btn">Send Reset Link</button>
            </form>

            <div class="form-link">
                <a href="{{ route('login') }}">Back to sign in</a>
            </div>
        </div>
    </div>
@endsection