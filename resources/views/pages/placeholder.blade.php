@extends('layouts.app')

@section('title', $title ?? 'HPE System')

@section('content')
<h2>{{ $title ?? 'Halaman' }}</h2>

<div style="margin-top: 2rem; padding: 2rem; background: #f9fafb; border-radius: 8px;">
    <p style="font-size: 1.1rem; margin-bottom: 1rem;">{{ $message ?? 'Fitur ini tersedia via API.' }}</p>
    
    @if(isset($api_endpoint))
    <div style="margin-top: 1.5rem;">
        <p style="font-weight: 600; margin-bottom: 0.5rem;">API Endpoint:</p>
        <code style="background: #e5e7eb; padding: 0.5rem 1rem; border-radius: 4px; display: inline-block;">
            {{ $api_endpoint }}
        </code>
    </div>
    
    <div style="margin-top: 1.5rem;">
        <p style="font-weight: 600; margin-bottom: 0.5rem;">Cara Menggunakan:</p>
        <ol style="margin-left: 1.5rem; line-height: 1.8;">
            <li>Login via API: <code>POST /api/auth/login</code> dengan email & password</li>
            <li>Dapatkan token dari response</li>
            <li>Gunakan token di header: <code>Authorization: Bearer {token}</code></li>
            <li>Akses endpoint di atas dengan token tersebut</li>
        </ol>
    </div>
    
    <div style="margin-top: 1.5rem;">
        <p style="font-weight: 600; margin-bottom: 0.5rem;">Contoh dengan cURL:</p>
        <pre style="background: #1f2937; color: #f9fafb; padding: 1rem; border-radius: 4px; overflow-x: auto;"><code>curl -X GET http://localhost:8000{{ $api_endpoint }} \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"</code></pre>
    </div>
    @endif

    <div style="margin-top: 2rem;">
        <a href="{{ route('dashboard') }}" class="btn">Kembali ke Dashboard</a>
    </div>
</div>
@endsection

