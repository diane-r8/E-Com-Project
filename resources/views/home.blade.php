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


<!-- Review Section
<section class="reviews-section">
    <div class="container">
        <h2 class="text-center text-white">Customer Reviews</h2>
        <div id="reviewsCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                @php
                    $reviews = [
                        ['name' => 'Aiah', 'image' => 'aiah.jpg', 'text' => 'Sobrang ganda ng packaging at quality! Perfect pang regalo!'],
                        ['name' => 'Colet', 'image' => 'colet.jpg', 'text' => 'Ang bilis ng transaction! Napaka-accommodating ng seller.'],
                        ['name' => 'Maloi', 'image' => 'maloi.jpg', 'text' => 'Super elegant ng bouquet! Mas maganda sa personal.'],
                        ['name' => 'Gwen', 'image' => 'gwen.jpg', 'text' => 'Highly recommended! Maganda ang details, sulit na sulit.'],
                        ['name' => 'Stacey', 'image' => 'stacey.jpg', 'text' => 'Worth every peso! Napaka-creative ng pagkakagawa.'],
                        ['name' => 'Jhoanna', 'image' => 'jhoanna.jpg', 'text' => 'Maganda ang craftsmanship, mukhang mamahalin!'],
                        ['name' => 'Mikha', 'image' => 'mikha.jpg', 'text' => 'Legit ang quality! Ang ganda ng design, unique at classy.'],
                        ['name' => 'Sheena', 'image' => 'sheena.jpg', 'text' => 'Napaka-pulido ng gawa! At super friendly ng seller.']
                    ];
                @endphp

                @foreach ($reviews as $index => $review)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <div class="review-card">
                            <img src="{{ asset('images/' . $review['image']) }}" alt="{{ $review['name'] }}" class="review-img">
                            <h4>{{ $review['name'] }}</h4>
                            <div class="stars">
                                @for ($i = 0; $i < 5; $i++)
                                    <i class="bi bi-star-fill"></i>
                                @endfor
                            </div>
                            <p>"{{ $review['text'] }}"</p>
                        </div>
                    </div>
                @endforeach
            </div>

             Carousel Controls 
            <button class="carousel-control-prev" type="button" data-bs-target="#reviewsCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#reviewsCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
        </div>
    </div>
</section> -->


@endsection