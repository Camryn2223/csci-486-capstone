@extends('layouts.app')

@section('content')
    <h1>Set New Password</h1>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <label>Email<br>
            <input type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
        </label>
        <br><br>

        <label>New Password<br>
            <input type="password" name="password" required autocomplete="new-password">
        </label>
        <br><br>

        <label>Confirm New Password<br>
            <input type="password" name="password_confirmation" required autocomplete="new-password">
        </label>
        <br><br>

        <button type="submit">Reset Password</button>
    </form>
@endsection