@extends('layouts.app')

@section('title', 'Sign In')

@section('content')
    <div class="centered-content">
        <div class="form-box">
            <h2>Sign In</h2>

            @if (session('status'))
                <p style="color: #9dffb0; text-align: center;">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required autofocus autocomplete="username">

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">

                <label style="display: flex; align-items: center; margin-bottom: 18px; color: #bdbdbd; cursor: pointer;">
                    <input type="checkbox" name="remember"> Remember me
                </label>

                <button type="submit" class="btn">Sign In</button>
            </form>

            @if (Route::has('password.request'))
                <div class="form-link">
                    <a href="{{ route('password.request') }}">Forgot your password?</a>
                </div>
            @endif

            <div class="form-link">
                Don't have an account?
                <a href="{{ route('register') }}">Sign Up</a>
            </div>
        </div>
    </div>
@endsection