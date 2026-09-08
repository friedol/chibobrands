@php
    // Prefer the compact PDF logo, fall back to full logo
    $__logoPaths = [
        public_path('images/logo-pdf.png'),
        public_path('images/logo.png'),
        public_path('images/logo.webp'),
    ];
    $__logoSrc = null;
    foreach ($__logoPaths as $__path) {
        if (file_exists($__path) && is_readable($__path)) {
            $__ext = strtolower(pathinfo($__path, PATHINFO_EXTENSION));
            $__mime = $__ext === 'webp' ? 'image/webp' : 'image/png';
            $__logoSrc = 'data:' . $__mime . ';base64,' . base64_encode(file_get_contents($__path));
            break;
        }
    }
    $__logoStyle = $logoStyle ?? 'max-height:60px;max-width:180px;object-fit:contain;';
    $__logoAlt   = $logoAlt   ?? 'CHIBO BRANDS';
@endphp
@if($__logoSrc)
    <img src="{{ $__logoSrc }}" alt="{{ $__logoAlt }}" style="{{ $__logoStyle }}">
@endif
