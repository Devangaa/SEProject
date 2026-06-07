{{-- ============================================================================= --}}
{{-- FILE: components/logo-icon.blade.php --}}
{{-- HALAMAN: Logo Icon Komponen --}}
{{-- DESKRIPSI: Logo HydroMart dibuat dengan CSS/SVG (bukan favicon) --}}
{{-- ============================================================================= --}}

<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" class="{{ $class ?? 'w-11 h-11' }}" fill="none">
    <!-- Outline utama - brown/coklat dengan amber accent -->
    <defs>
        <linearGradient id="brownGradient" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" style="stop-color:#b45309;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#78350f;stop-opacity:1" />
        </linearGradient>
        <linearGradient id="amberGradient" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" style="stop-color:#f59e0b;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#d97706;stop-opacity:1" />
        </linearGradient>
    </defs>

    <!-- Container bunga/droplet - Brown base -->
    <circle cx="50" cy="40" r="28" fill="url(#brownGradient)" opacity="0.95"/>
    
    <!-- Kelopak atas 1 (amber) -->
    <circle cx="50" cy="20" r="12" fill="url(#amberGradient)"/>
    
    <!-- Kelopak atas 2 (amber) -->
    <circle cx="65" cy="28" r="12" fill="url(#amberGradient)" opacity="0.9"/>
    
    <!-- Kelopak kanan (amber) -->
    <circle cx="65" cy="48" r="12" fill="url(#amberGradient)" opacity="0.85"/>
    
    <!-- Kelopak bawah (amber) -->
    <circle cx="50" cy="60" r="12" fill="url(#amberGradient)" opacity="0.9"/>
    
    <!-- Kelopak kiri (amber) -->
    <circle cx="35" cy="48" r="12" fill="url(#amberGradient)" opacity="0.85"/>
    
    <!-- Kelopak atas kiri (amber) -->
    <circle cx="35" cy="28" r="12" fill="url(#amberGradient)" opacity="0.9"/>
    
    <!-- Pusat (brown gelap) -->
    <circle cx="50" cy="40" r="10" fill="#5f3107"/>
    
    <!-- Stem / Tangkai (brown) -->
    <path d="M 50 68 Q 48 80 45 90" stroke="#92400e" stroke-width="4" fill="none" stroke-linecap="round"/>
    
    <!-- Leaf kecil di tangkai -->
    <ellipse cx="42" cy="75" rx="5" ry="8" fill="#f59e0b" transform="rotate(-30 42 75)"/>
</svg>
</svg>
