@extends('layouts.app')

@section('content')
    <h1>Sign In</h1>

    @if (session('status'))
        <p style="color:green">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <label>Email<br>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        </label>
        <br><br>

        <label>Password<br>
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        <br><br>

        <label>
            <input type="checkbox" name="remember"> Remember me
        </label>
        <br><br>

        <button type="submit">Sign In</button>

        @if (Route::has('password.request'))
            | <a href="{{ route('password.request') }}">Forgot your password?</a>
        @endif
    </form>

    <br><p>Don't have an account? <a href="{{ route('register') }}">Create one</a></p>
@endsection