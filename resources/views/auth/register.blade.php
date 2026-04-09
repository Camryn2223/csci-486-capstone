@extends('layouts.app')

@section('title', 'Sign Up')

@section('content')
    <div class="centered-content">
        <div class="form-box">
            <h2>Create an Account</h2>

            @if ($isFirstUser)
                <p class="text-success text-center fs-14">
                    You are creating the first account. You will be registered as the chairman.
                </p>
            @elseif ($inviteCode && $inviteValid)
                <p class="text-success text-center fs-14">
                    Your invite link is valid. Fill in your details below to join.
                </p>
            @elseif ($inviteCode && ! $inviteValid)
                <p class="text-danger text-center fs-14">
                    This invite link is invalid or has already been used. Enter a valid invite code below.
                </p>
            @else
                <p class="text-muted text-center fs-14">
                    Enter the invite code provided by your organization to register.
                </p>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Enter your full name" required autofocus autocomplete="name">

                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required autocomplete="username">

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required autocomplete="new-password">

                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Re-enter your password" required autocomplete="new-password">

                @if (! $isFirstUser)
                    <label for="invite_code">Invite Code</label>
                    <input
                        type="text"
                        id="invite_code"
                        name="invite_code"
                        value="{{ old('invite_code', $inviteCode) }}"
                        placeholder="Enter your invite code"
                        required
                        autocomplete="off"
                        class="uppercase"
                        {{ ($inviteCode && $inviteValid) ? 'readonly' : '' }}
                    >
                    @if ($inviteCode && $inviteValid)
                        <small class="text-success d-block mb-18" style="margin-top: -12px;">
                            Pre-filled from your invite link.
                        </small>
                    @endif
                @endif

                <button type="submit" class="btn">Create Account</button>
            </form>

            <div class="form-link">
                Already have an account?
                <a href="{{ route('login') }}">Sign In</a>
            </div>
        </div>
    </div>
@endsection