@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 400px; margin: auto; text-align: center;">
    <h2>Verify OTP</h2>

    @if (session('message'))
        <div style="color: green;">{{ session('message') }}</div>
    @endif

    @if ($errors->any())
        <div style="color: red;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('verify.otp') }}">
        @csrf
        <label>Enter OTP</label>
        <input type="text" name="otp" required autofocus style="width: 100%; padding: 8px;">
        <button type="submit">Verify OTP</button>
    </form>

    <p>Didn't receive an OTP? <a href="{{ route('login') }}">Login again</a>.</p>
</div>
@endsection
