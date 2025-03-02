@extends('layouts.app')

@section('content')
<div class="container about-section">
    <div class="row align-items-center">
        <!-- Left Section: Images -->
        <div class="col-lg-6 text-center">
            <img src="{{ asset('images/founders.jpg') }}" alt="Founder" class="founder-img mb-3">
            <div class="logo-container d-flex justify-content-center">
                <img src="{{ asset('images/logo1.png') }}" alt="CNW Logo 1" class="about-logo me-2">
                <img src="{{ asset('images/logo2.png') }}" alt="CNW Logo 2" class="about-logo ms-2">
            </div>
        </div>

        <!-- Right Section: Text Content -->
        <div class="col-lg-6">
            <h1 class="about-title">About Us</h1>
            <p class="about-text">
                Founded in 2024, Crafts N’ Wraps (CNW) began as a small passion project. Today, we are proud to have 
                grown into a beloved local business known for our handcrafted gifts, creative arrangements, and 
                personalized accessories. 
            </p>
            <p class="about-text">
                From elegant bouquets to unique handmade keepsakes, we offer a variety of thoughtfully crafted 
                products perfect for any occasion. Whether you're celebrating a milestone, expressing gratitude, or 
                simply looking for a heartfelt gift, our creations are designed to make every moment special.  
            </p>
            <p class="about-text">
                Guided by our motto, <strong>"Creating Eternal Treasures Embraced by the Heart,"</strong> we are 
                dedicated to providing high-quality, meaningful gifts that leave a lasting impression. At CNW, 
                every piece is made with passion, creativity, and a deep appreciation for the art of gifting.
            </p>
        </div>
    </div>

    <div class="row mt-4 text-center">
        <p class="about-text text-muted mb-0"><em>Crafts N’ Wraps est. 2024</em></p>
        <p class="about-text text-muted"><em>Founded by Princess Diane Rosana and Johanna Fae Balasta</em></p>
    </div>
</div>
@endsection  
