<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Management</title>
</head>
<body>

<nav>
    @auth
        <a href="{{ route('organizations.index') }}">Organizations</a> |
        <a href="{{ route('dashboard') }}">Dashboard</a> |
        <a href="{{ route('two-factor.show') }}">Settings</a> |
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit">Logout</button>
        </form>
    @else
        <a href="{{ route('login') }}">Login</a> |
        <a href="{{ route('register') }}">Register</a>
    @endauth
</nav>

<hr>

@if (session('success'))
    <p style="color:green"><strong>{{ session('success') }}</strong></p>
@endif

@if (session('error'))
    <p style="color:red"><strong>{{ session('error') }}</strong></p>
@endif

@if ($errors->any())
    <ul style="color:red">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

@yield('content')

</body>
</html>