<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HPE System')</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#4F46E5">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { background: #4F46E5; color: white; padding: 1rem 2rem; margin-bottom: 2rem; }
        .header h1 { font-size: 1.5rem; }
        .nav { background: white; padding: 1rem; margin-bottom: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .nav a { margin-right: 1rem; color: #4F46E5; text-decoration: none; }
        .nav a:hover { text-decoration: underline; }
        .content { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .btn { display: inline-block; padding: 0.5rem 1rem; background: #4F46E5; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .btn:hover { background: #4338CA; }
        .btn-secondary { background: #6B7280; }
        .btn-secondary:hover { background: #4B5563; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        table th, table td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #e5e7eb; }
        table th { background: #f9fafb; font-weight: 600; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 4px; }
        .alert { padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="header">
        <h1>HPE System - Harga Perkiraan Estimasi</h1>
    </div>

    <div class="container">
        <nav class="nav">
            <a href="{{ url('/dashboard') }}">Dashboard</a>
            <a href="{{ url('/products') }}">Produk</a>
            <a href="{{ url('/components') }}">Komponen</a>
            <a href="{{ url('/purchase-histories') }}">Riwayat Pengadaan</a>
            <a href="{{ url('/hpe/results') }}">Hasil HPE</a>
            <a href="{{ url('/hpe/calculate') }}">Hitung HPE</a>
            <a href="{{ url('/exchange-rates') }}">Kurs</a>
            <a href="{{ url('/reporting') }}">Laporan</a>
            <a href="{{ url('/audit-logs') }}">Audit Log</a>
            <span style="margin-left: auto;">
                <span id="user-info" style="margin-right: 1rem; color: #6b7280;"></span>
                <a href="#" onclick="logout(); return false;">Logout</a>
            </span>
        </nav>

        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            @yield('content')
        </div>
    </div>

    @stack('scripts')
    <script>
        // Load user info and handle logout
        document.addEventListener('DOMContentLoaded', function() {
            const apiToken = localStorage.getItem('api_token') || '';
            if (apiToken) {
                fetch('/api/auth/me', {
                    headers: {
                        'Authorization': 'Bearer ' + apiToken,
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.user) {
                        document.getElementById('user-info').textContent = data.user.name + ' (' + data.user.role + ')';
                    }
                })
                .catch(() => {
                    // Token invalid, redirect to login
                    localStorage.removeItem('api_token');
                    window.location.href = '/login';
                });
            } else {
                // No token, redirect to login
                if (window.location.pathname !== '/login') {
                    window.location.href = '/login';
                }
            }
        });

        function logout() {
            const apiToken = localStorage.getItem('api_token') || '';
            if (apiToken) {
                fetch('/api/auth/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + apiToken,
                        'Accept': 'application/json'
                    }
                })
                .finally(() => {
                    localStorage.removeItem('api_token');
                    window.location.href = '/login';
                });
            } else {
                window.location.href = '/login';
            }
        }

        // Register service worker with error handling
        if ('serviceWorker' in navigator) {
            // Unregister all service workers first to clear old cache
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for(let registration of registrations) {
                    registration.unregister().then(function(success) {
                        if (success) {
                            console.log('Old service worker unregistered');
                        }
                    });
                }
            });

            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js', { scope: '/' })
                    .then(reg => {
                        console.log('Service Worker registered successfully');
                        reg.update();
                    })
                    .catch(err => {
                        console.log('Service Worker registration failed (non-critical):', err);
                    });
            });
        }
    </script>
</body>
</html>

