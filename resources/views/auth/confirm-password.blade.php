@extends('layouts.app')

@section('content')
    <div class="centered-content">
        <div class="form-box">
            <h2>Confirm Password</h2>

            <p style="color: #bdbdbd; text-align: center; font-size: 14px;">Please confirm your password before continuing.</p>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <label>Password</label>
                <input type="password" name="password" required autofocus autocomplete="current-password">

                <button type="submit" class="btn">Confirm</button>
            </form>
        </div>
    </div>
@endsection