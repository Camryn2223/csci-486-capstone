@extends('layouts.app')

@section('content')
    <div class="centered-content">
        <div class="form-box">
            <h2>Set New Password</h2>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <label>Email</label>
                <input type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">

                <label>New Password</label>
                <input type="password" name="password" required autocomplete="new-password">

                <label>Confirm New Password</label>
                <input type="password" name="password_confirmation" required autocomplete="new-password">

                <button type="submit" class="btn">Reset Password</button>
            </form>
        </div>
    </div>
@endsection