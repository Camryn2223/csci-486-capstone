@extends('layouts.app')

@section('content')
    <h1>Confirm Password</h1>

    <p>Please confirm your password before continuing.</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <label>Password<br>
            <input type="password" name="password" required autofocus autocomplete="current-password">
        </label>
        <br><br>

        <button type="submit">Confirm</button>
    </form>
@endsection