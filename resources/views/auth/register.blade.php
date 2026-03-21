@extends('layouts.app')

@section('content')
    <h1>Create Account</h1>

    @if ($isFirstUser)
        <p><em>You are creating the first account. You will be registered as the chairman.</em></p>
    @elseif ($inviteCode && $inviteValid)
        <p style="color:green"><em>Your invite link is valid. Fill in your details below to join.</em></p>
    @elseif ($inviteCode && ! $inviteValid)
        <p style="color:red"><em>This invite link is invalid or has already been used. Enter a valid invite code manually below.</em></p>
    @else
        <p><em>Enter the invite code provided by your organization to register.</em></p>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <label>Name<br>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
        </label>
        <br><br>

        <label>Email<br>
            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
        </label>
        <br><br>

        <label>Password<br>
            <input type="password" name="password" required autocomplete="new-password">
        </label>
        <br><br>

        <label>Confirm Password<br>
            <input type="password" name="password_confirmation" required autocomplete="new-password">
        </label>
        <br><br>

        @if (! $isFirstUser)
            <label>Invite Code<br>
                <input
                    type="text"
                    name="invite_code"
                    value="{{ old('invite_code', $inviteCode) }}"
                    required
                    autocomplete="off"
                    style="text-transform:uppercase"
                    {{ ($inviteCode && $inviteValid) ? 'readonly' : '' }}
                >
            </label>
            @if ($inviteCode && $inviteValid)
                <small>Pre-filled from your invite link.</small>
            @endif
            <br><br>
        @endif

        <button type="submit">Create Account</button>
    </form>

    <br><p>Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
@endsection