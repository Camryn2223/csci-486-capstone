<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HireFlow')</title>
    
    <script>
        (() => {
            const stored = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            // Prioritize stored preference, fallback to system preference
            const useDark = stored === 'dark' || (!stored && prefersDark);
            document.documentElement.classList.toggle('dark', useDark);
        })();
    </script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        @auth
            <h1>
                HireFlow
                <span class="subtag">({{ auth()->user()->name }} - {{ auth()->user()->role }})</span>
            </h1>

            <div class="nav-right">
                @php
                    $user = auth()->user();
                    $orgs = $user->isChairman() ? $user->ownedOrganizations() : $user->organizations();
                    $dashRoute = $orgs->count() === 1 ? route('organizations.show', $orgs->first()) : route('dashboard');
                @endphp
                <a href="{{ $dashRoute }}">Dashboard</a>
                <a href="{{ route('organizations.index') }}">Organizations</a>
                <a href="{{ route('two-factor.show') }}">Settings</a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline m-0">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            </div>
        @else
            <h1>HireFlow</h1>

            <div class="nav-right">
                <a href="{{ route('login') }}">Sign In</a>
                <a href="{{ route('register') }}">Sign Up</a>
            </div>
        @endauth
    </div>

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="flash-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="flash-error toast">{{ session('error') }}</div>
    @endif

    <!-- Page Content -->
    <div class="page-content">
        @if ($errors->any())
            <div class="container container-wide pb-0" style="padding-top: 30px; margin-bottom: -10px;">
                <div class="static-error-box">
                    <strong class="text-danger d-block mb-10">Please correct the following errors:</strong>
                    <ul class="m-0 pl-20 text-danger fs-14">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
        
        @yield('content')
    </div>

    <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle Theme">
        🌓
    </button>

    @stack('scripts')
</body>
</html>