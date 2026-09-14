<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('code') — @yield('title') | Perpustakaan Kejati Jawa Barat</title>
    <x-favicon />
    <link rel="stylesheet" href="{{ asset('css/error.css') }}">
</head>

<body>
    <a class="skip-link" href="#error-content">Lewati ke informasi kesalahan</a>
    <main class="error-shell" id="error-content" tabindex="-1">
        <article class="error-document" aria-labelledby="error-title">
            <header class="error-masthead">
                <img src="{{ asset('images/logo.svg') }}" alt="" width="35" height="38">
                <span>
                    <span class="error-institution">Kejaksaan Tinggi Jawa Barat</span>
                    <span class="error-library">Perpustakaan Digital</span>
                </span>
            </header>
            <div class="error-content">
                <div class="error-status-panel" aria-hidden="true">
                    <span class="error-status">@yield('code')</span>
                </div>
                <div class="error-message">
                    <p class="error-eyebrow">Status layanan</p>
                    <h1 id="error-title">@yield('title')</h1>
                    <p class="error-description">@yield('message')</p>
                    <div class="error-actions">
                        <a class="error-home-link" href="{{ url('/') }}">
                            Kembali ke beranda <span class="error-arrow" aria-hidden="true">→</span>
                        </a>
                        <span class="error-reference">Kode referensi: HTTP @yield('code')</span>
                    </div>
                </div>
            </div>
        </article>
    </main>
</body>

</html>
