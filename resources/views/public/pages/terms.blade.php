@extends('layouts.app')

@section('title', 'Terms & Conditions - CHIBO BRAND')
@section('description', 'Read our terms and conditions to understand the rules and regulations for using our services.')

@section('content')
<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="page-title">Terms & Conditions</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Terms & Conditions</li>
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
                    
                    <h3>1. Acceptance of Terms</h3>
                    <p>By accessing and using this website, you accept and agree to be bound by the terms and provision of this agreement.</p>
                    
                    <h3>2. Use License</h3>
                    <p>Permission is granted to temporarily download one copy of the materials on CHIBO BRAND's website for personal, non-commercial transitory viewing only.</p>
                    
                    <h3>3. Disclaimer</h3>
                    <p>The materials on CHIBO BRAND's website are provided on an 'as is' basis. CHIBO BRAND makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties.</p>
                    
                    <h3>4. Limitations</h3>
                    <p>In no event shall CHIBO BRAND or its suppliers be liable for any damages arising out of the use or inability to use the materials on CHIBO BRAND's website.</p>
                    
                    <h3>5. Accuracy of Materials</h3>
                    <p>The materials appearing on CHIBO BRAND's website could include technical, typographical, or photographic errors.</p>
                    
                    <h3>6. Links</h3>
                    <p>CHIBO BRAND has not reviewed all of the sites linked to our website and is not responsible for the contents of any such linked site.</p>
                    
                    <h3>7. Modifications</h3>
                    <p>CHIBO BRAND may revise these terms of service for its website at any time without notice.</p>
                    
                    <h3>8. Governing Law</h3>
                    <p>These terms and conditions are governed by and construed in accordance with the laws of Tanzania.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
