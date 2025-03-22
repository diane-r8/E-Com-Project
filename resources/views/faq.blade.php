@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <h1 class="text-center">Frequently Asked Questions (FAQ)</h1>

        <div class="page-content mt-4">
            <!-- Accordion for General Questions -->
            <div class="accordion" id="generalQuestions">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingGeneral">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGeneral" aria-expanded="false" aria-controls="collapseGeneral">
                            General Questions
                        </button>
                    </h2>
                    <div id="collapseGeneral" class="accordion-collapse collapse" aria-labelledby="headingGeneral" data-bs-parent="#generalQuestions">
                        <div class="accordion-body">
                            <!-- Question 1 -->
                            <div class="accordion" id="generalQ1">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingQ1">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQ1" aria-expanded="false" aria-controls="collapseQ1">
                                            Q: What products do you offer?
                                        </button>
                                    </h2>
                                    <div id="collapseQ1" class="accordion-collapse collapse" aria-labelledby="headingQ1" data-bs-parent="#generalQ1">
                                        <div class="accordion-body">
                                            A: We offer a variety of custom bouquets, accessories, and gift items.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Question 2 -->
                            <div class="accordion" id="generalQ2">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingQ2">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQ2" aria-expanded="false" aria-controls="collapseQ2">
                                            Q: How do I place an order?
                                        </button>
                                    </h2>
                                    <div id="collapseQ2" class="accordion-collapse collapse" aria-labelledby="headingQ2" data-bs-parent="#generalQ2">
                                        <div class="accordion-body">
                                            A: You can place an order online through our website or contact us directly for customized products.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accordion for Order Questions -->
            <div class="accordion" id="orderQuestions">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOrder">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOrder" aria-expanded="false" aria-controls="collapseOrder">
                            Order Questions
                        </button>
                    </h2>
                    <div id="collapseOrder" class="accordion-collapse collapse" aria-labelledby="headingOrder" data-bs-parent="#orderQuestions">
                        <div class="accordion-body">
                            <!-- Question 1 -->
                            <div class="accordion" id="orderQ1">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOrderQ1">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOrderQ1" aria-expanded="false" aria-controls="collapseOrderQ1">
                                            Q: Can I cancel or change my order after placing it?
                                        </button>
                                    </h2>
                                    <div id="collapseOrderQ1" class="accordion-collapse collapse" aria-labelledby="headingOrderQ1" data-bs-parent="#orderQ1">
                                        <div class="accordion-body">
                                            A: No, once the order is processed, it cannot be cancelled or changed.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Question 2 -->
                            <div class="accordion" id="orderQ2">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOrderQ2">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOrderQ2" aria-expanded="false" aria-controls="collapseOrderQ2">
                                            Q: Do you accept rush orders?
                                        </button>
                                    </h2>
                                    <div id="collapseOrderQ2" class="accordion-collapse collapse" aria-labelledby="headingOrderQ2" data-bs-parent="#orderQ2">
                                        <div class="accordion-body">
                                            A: Yes, we do accept rush orders, but a rush fee may apply depending on availability.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accordion for Payment & Delivery Questions -->
            <div class="accordion" id="paymentDeliveryQuestions">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingPayment">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePayment" aria-expanded="false" aria-controls="collapsePayment">
                            Payment & Delivery
                        </button>
                    </h2>
                    <div id="collapsePayment" class="accordion-collapse collapse" aria-labelledby="headingPayment" data-bs-parent="#paymentDeliveryQuestions">
                        <div class="accordion-body">
                            <!-- Question 1 -->
                            <div class="accordion" id="paymentQ1">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingPaymentQ1">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePaymentQ1" aria-expanded="false" aria-controls="collapsePaymentQ1">
                                            Q: What payment methods do you accept?
                                        </button>
                                    </h2>
                                    <div id="collapsePaymentQ1" class="accordion-collapse collapse" aria-labelledby="headingPaymentQ1" data-bs-parent="#paymentQ1">
                                        <div class="accordion-body">
                                            A: We accept payments via GCash and COD (Cash On Delivery).
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Question 2 -->
                            <div class="accordion" id="paymentQ2">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingPaymentQ2">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePaymentQ2" aria-expanded="false" aria-controls="collapsePaymentQ2">
                                            Q: Do you offer delivery?
                                        </button>
                                    </h2>
                                    <div id="collapsePaymentQ2" class="accordion-collapse collapse" aria-labelledby="headingPaymentQ2" data-bs-parent="#paymentQ2">
                                        <div class="accordion-body">
                                            A: Yes, we offer delivery services to selected areas.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
