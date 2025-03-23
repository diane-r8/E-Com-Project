@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="contact-title text-center">Get in Touch</h1>
    <p class="contact-subtitle text-center">
        We’d love to hear from you! Reach out to us through the details below or send us a message.
    </p>

    <div class="row align-items-stretch">
        <!-- Left Column: Contact Info & Social Media in Containers -->
        <div class="col-md-6 d-flex flex-column justify-content-between">
            
            <!-- 🔹 Contact Info Section -->
            <div class="card contact-info-box mb-4">
                <div class="contact-item">
                    <div class="contact-icon"><i class="bi bi-geo-alt-fill"></i></div>
                    <div class="contact-text">
                        <h4>Address</h4>
                        <p>Purok 6 Sta. Misericordia, Sto. Domingo, Albay</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon"><i class="bi bi-telephone-fill"></i></div>
                    <div class="contact-text">
                        <h4>Phone</h4>
                        <p>+63 9706104541</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon"><i class="bi bi-envelope-fill"></i></div>
                    <div class="contact-text">
                        <h4>Email</h4>
                        <p>craftsnwraps24@gmail.com</p>
                    </div>
                </div>
            </div>

            <!-- 🔹 Social Media Section -->
            <div class="card social-box">
                <h3 class="text-center">Follow Us</h3>
                <div class="social-details d-flex justify-content-center gap-4 mt-3">
                    <a href="https://www.facebook.com/craftsnwrapsofficial" target="_blank" class="social-link">
                        <i class="bi bi-facebook social-icon"></i>
                    </a>
                    <a href="https://www.instagram.com/craftsnwraps24/" target="_blank" class="social-link">
                        <i class="bi bi-instagram social-icon"></i>
                    </a>
                    <a href="https://www.tiktok.com/@crafts.n.wraps?_t=ZS-8uVjjUlR2zi&_r=1" target="_blank" class="social-link">
                        <i class="bi bi-tiktok social-icon"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Column: Modernized Contact Form -->
        <div class="col-md-6">
            <div class="card contact-form-box h-100">
                <h2 class="form-title text-center">Send Us a Message</h2>
                <form action="{{ route('contact.submit') }}" method="POST" class="d-flex flex-column h-100" id="contactForm">
                    @csrf
                    <div class="mb-3">
                        <input type="text" class="form-control modern-input" id="name" name="name" placeholder="Full Name" required>
                    </div>

                    <div class="mb-3">
                        <input type="email" class="form-control modern-input" id="email" name="email" placeholder="Email" required>
                    </div>

                    <div class="mb-3">
                        <textarea class="form-control modern-textarea" id="message" name="message" rows="5" placeholder="Type your Message..." required></textarea>
                    </div>

                    <button type="submit" class="btn custom-btn w-100 modern-btn">Send</button>
                </form>
            </div>
        </div>
    </div>

    <!-- 🔹 Google Maps (At Bottom) -->
    <div class="map-container mt-5">
        <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3883.551908335823!2d123.7566617735943!3d13.253413608640127!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33a1ab004176e2f3%3A0xc635f93fe5d776fa!2sCrafts%20N&#39;%20Wraps!5e0!3m2!1sen!2sph!4v1741492696154!5m2!1sen!2sph" 
            width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy">
        </iframe>
    </div>
</div>

<!--Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="successModalLabel">Message Sent!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                🎉 Thank you! Your message has been successfully sent. We'll get back to you soon!
            </div>
        </div>
    </div>
</div>

<!--Show Modal if Session Success -->
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
        });
    </script>
@endif

@endsection
