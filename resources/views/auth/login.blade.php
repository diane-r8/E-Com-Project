@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Login</h1>

    @if ($errors->any())
        <div>
            @foreach ($errors->all() as $error)
                <p style="color: red;">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div>
            <label>Email</label>
            <input type="email" name="email" required autofocus>
        </div>
        <div>
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div>
            <input type="checkbox" name="remember"> Remember Me
        </div>
        <button type="submit">Login</button>
    </form>

    <p>
        Don't have an account? <a href="{{ route('register') }}">Register here</a>.
    </p>

    <p>
        <a href="{{ route('password.request') }}">Forgot your password?</a>
    </p>
</div>
@endsection
