@extends('layouts.app')

@section('title', 'Welcome')

@section('content')
    <div class="centered-content">
        <div class="form-box" style="width: 500px; text-align: center;">
            <h2>Interview Management System</h2>
            <p style="color: #bdbdbd; margin-bottom: 30px;">
                Manage your organization's hiring process — from job postings to interviews.
            </p>

            @auth
                <a href="{{ route('dashboard') }}" class="btn" style="width: 100%;">Go to Dashboard</a>
            @else
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <a href="{{ route('login') }}" class="btn">Sign In</a>
                    <a href="{{ route('register') }}" class="btn" style="background: #24282d; border: 1px solid #3a3f45;">Sign Up</a>
                </div>
            @endauth
        </div>
    </div>
@endsection