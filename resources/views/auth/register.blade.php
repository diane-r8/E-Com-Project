@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4 max-w-md">
    <h1 class="text-2xl font-bold mb-4 text-center">Register</h1>

    {{-- Display Validation Errors --}}
    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="fname" class="block font-semibold">First Name</label>
            <input type="text" id="fname" name="fname" value="{{ old('fname') }}" required class="w-full border p-2 rounded">
        </div>

        <div>
            <label for="lname" class="block font-semibold">Last Name</label>
            <input type="text" id="lname" name="lname" value="{{ old('lname') }}" required class="w-full border p-2 rounded">
        </div>

        <div>
            <label for="username" class="block font-semibold">Username</label>
            <input type="text" id="username" name="username" value="{{ old('username') }}" required class="w-full border p-2 rounded">
        </div>

        <div>
            <label for="email" class="block font-semibold">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required class="w-full border p-2 rounded">
        </div>

        <div>
            <label for="password" class="block font-semibold">Password</label>
            <input type="password" id="password" name="password" required class="w-full border p-2 rounded">
        </div>

        <div>
            <label for="password_confirmation" class="block font-semibold">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required class="w-full border p-2 rounded">
        </div>

        <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
            Register
        </button>

        <p class="text-center mt-4">
            Already have an account? <a href="{{ route('login') }}" class="text-blue-500 underline">Login here</a>.
        </p>
    </form>
</div>
@endsection
