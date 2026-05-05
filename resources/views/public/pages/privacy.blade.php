@extends('public.layouts.app')

@section('title', 'Privacy Policy - CHIBO BRAND')
@section('description', 'Read our privacy policy to understand how we collect, use, and protect your personal information.')

@section('content')
<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="page-title">Privacy Policy</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Privacy Policy</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted">Last updated: {{ date('F d, Y') }}</p>
                    
                    <h3>1. Information We Collect</h3>
                    <p>We collect information you provide directly to us, such as when you create an account, place an order, or contact us for support.</p>
                    
                    <h3>2. How We Use Your Information</h3>
                    <p>We use the information we collect to provide, maintain, and improve our services, process orders, and communicate with you.</p>
                    
                    <h3>3. Information Sharing</h3>
                    <p>We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except as described in this policy.</p>
                    
                    <h3>4. Data Security</h3>
                    <p>We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.</p>
                    
                    <h3>5. Contact Us</h3>
                    <p>If you have any questions about this Privacy Policy, please contact us at:</p>
                    <ul>
                        <li>Email: info@chibobrand.com</li>
                        <li>Phone: +255 687 183 330</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
