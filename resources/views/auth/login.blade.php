@extends('layouts.app')

@section('content')
<div class="login-page">
    <div class="login-container">
        <!-- Left side: Image -->
        <div class="login-image">
            <img src="{{ asset('images/login-image.png') }}" alt="Login Image">
        </div>

        <!-- Right side: Login Form -->
        <div class="login-form">
            <div class="card">
                <h1>Login</h1>

                @if ($errors->any())
                    <div>
                        @foreach ($errors->all() as $error)
                            <p class="error">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div>
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required autofocus>
                    </div>
                    <div>
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="checkbox-label">
                        <input type="checkbox" name="remember"> 
                        <label for="remember">Remember Me</label>
                    </div>
                    <button type="submit">Login</button>
<!-- newly added -->
               <a href="{{ route('social.redirect', 'google') }}" class="btn btn-danger">Login with Google</a> 
                </form>

                <p>
                    Don't have an account? <a href="{{ route('register') }}">Register here</a>.
                </p>

                <p>
                    <a href="{{ route('password.request') }}">Forgot your password?</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
