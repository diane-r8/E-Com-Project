@extends('layouts.app')

@section('content')
<div class="container-fluid hero-section">
    <div class="row">
        <!-- Left Section with Static Flower Frame Image -->
        <div class="col-lg-6 flower-frame-container">
            <img src="{{ asset('images/flower-frame.png') }}" alt="Flower Frame" class="flower-frame">
        </div>

        <!-- Right Section with Title and Shop Now Button -->
        <div class="col-lg-6 d-flex flex-column justify-content-center align-items-center">
            <h1 class="hero-title">Your Go-To Shop For Handmade Bouquets, Gifts and Accessories</h1>
            <a href="{{ url('/products') }}" class="btn btn-primary shop-now-btn">Shop Now</a>
        </div>
    </div>
</div>
@endsection
