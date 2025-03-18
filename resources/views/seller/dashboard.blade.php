@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Seller Dashboard</h1>
    <p>Welcome, {{ auth()->user()->fname }} {{ auth()->user()->lname }}!</p>
    <p>User Type: {{ auth()->user()->user_type }}</p>
</div>
@endsection

