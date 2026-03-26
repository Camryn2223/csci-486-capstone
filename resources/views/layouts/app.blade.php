<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Interview Management')</title>
    <style>
        /* ===== Global Reset & Base ===== */
        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #1a1d21;
            color: #e6e6e6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a {
            color: #a97dff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* ===== Navbar ===== */
        .navbar {
            width: 100%;
            background: #6d3fa9;
            color: white;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #3e215c;
        }

        .navbar h1 {
            margin: 0;
            font-size: 22px;
            color: white;
        }

        .navbar .subtag {
            font-size: 14px;
            opacity: 0.7;
            margin-left: 6px;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-right a {
            color: white;
            text-decoration: none;
            font-size: 16px;
        }

        .nav-right a:hover {
            color: #e9d6ff;
        }

        .nav-right button {
            background: none;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            padding: 0;
            font-family: inherit;
        }

        .nav-right button:hover {
            color: #e9d6ff;
        }

        /* ===== Flash Messages ===== */
        .flash-success {
            background: #0f3d1e;
            color: #9dffb0;
            padding: 12px 20px;
            margin: 20px auto;
            max-width: 600px;
            border-radius: 6px;
            border: 1px solid #1a5c30;
        }

        .flash-error {
            background: #3d0f0f;
            color: #ff9d9d;
            padding: 12px 20px;
            margin: 20px auto;
            max-width: 600px;
            border-radius: 6px;
            border: 1px solid #5c1a1a;
        }

        .flash-error ul {
            margin: 0;
            padding-left: 20px;
        }

        /* ===== Common Elements ===== */
        .card {
            background: #24282d;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.4);
            border: 1px solid #2f343a;
        }

        .btn {
            display: inline-block;
            padding: 12px 20px;
            background: #6d3fa9;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }

        .btn:hover {
            background: #593287;
            text-decoration: none;
            color: white;
        }

        .btn-danger {
            background: #a33;
        }

        .btn-danger:hover {
            background: #822;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        label {
            font-size: 14px;
            color: #bdbdbd;
            display: block;
            margin-bottom: 4px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="date"],
        input[type="time"],
        input[type="number"],
        input[type="tel"],
        input[type="url"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            margin-bottom: 18px;
            border-radius: 6px;
            border: 1px solid #3a3f45;
            background: #1f2327;
            color: #e6e6e6;
            font-size: 14px;
            font-family: inherit;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        input[type="checkbox"] {
            margin-right: 6px;
        }

        h2 {
            color: #6d3fa9;
            margin-top: 0;
        }

        /* ===== Page Content ===== */
        .page-content {
            flex: 1;
            width: 100%;
        }

        /* ===== Centered Form Layout (for auth pages) ===== */
        .centered-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
        }

        .form-box {
            background: #24282d;
            padding: 40px;
            width: 380px;
            max-width: 100%;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
            border: 1px solid #2f343a;
            margin-top: 40px;
        }

        .form-box h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #cfcfcf;
            font-size: 24px;
        }

        .form-box .btn {
            width: 100%;
            margin-top: 10px;
        }

        .form-link {
            text-align: center;
            margin-top: 18px;
            font-size: 14px;
        }

        .form-link a {
            color: #a97dff;
        }

        /* ===== Dashboard Layout ===== */
        .container {
            padding: 30px;
            max-width: 900px;
            margin: 0 auto;
        }

        /* ===== Status Badges ===== */
        .status {
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 14px;
            display: inline-block;
        }

        .status-needs-review { background: #004d5e; color: #7eeaff; }
        .status-schedule-interview { background: #3a245a; color: #c7a6ff; }
        .status-awaiting-interview { background: #2f3a4a; color: #b7c7d9; }
        .status-awaiting-feedback { background: #4a3d00; color: #ffe58a; }
        .status-complete { background: #0f3d1e; color: #9dffb0; }

        /* ===== Entry Boxes ===== */
        .entry-box {
            margin-bottom: 10px;
            padding: 10px;
            background: #1f2327;
            border: 1px solid #3a3f45;
            border-radius: 6px;
        }

        .entry-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        /* ===== Tables ===== */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #2f343a;
        }

        th {
            color: #a97dff;
            font-size: 14px;
        }

        td {
            font-size: 14px;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        @auth
            <h1>
                Interview Management
                <span class="subtag">({{ auth()->user()->role }})</span>
            </h1>

            <div class="nav-right">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <a href="{{ route('organizations.index') }}">Organizations</a>
                <a href="{{ route('two-factor.show') }}">Settings</a>
                <form method="POST" action="{{ route('logout') }}" style="display:inline">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            </div>
        @else
            <h1>Interview Management</h1>

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
        <div class="flash-error">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="flash-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Page Content -->
    <div class="page-content">
        @yield('content')
    </div>

</body>
</html>