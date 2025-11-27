@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<h2>Dashboard</h2>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 2rem;">
    <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px;">
        <h3 style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Total Produk</h3>
        <p style="font-size: 2rem; font-weight: bold; color: #111827;" id="total-products">{{ $summary['total_products'] ?? 0 }}</p>
    </div>
    <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px;">
        <h3 style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Total Komponen</h3>
        <p style="font-size: 2rem; font-weight: bold; color: #111827;" id="total-components">{{ $summary['total_components'] ?? 0 }}</p>
    </div>
    <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px;">
        <h3 style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Kurs USD/IDR</h3>
        <p style="font-size: 2rem; font-weight: bold; color: #111827;" id="exchange-rate">{{ $exchange_rate['rate_value'] ?? '-' }}</p>
    </div>
    <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px;">
        <h3 style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">Total HPE</h3>
        <p style="font-size: 2rem; font-weight: bold; color: #111827;" id="total-hpe">{{ $summary['total_hpe_results'] ?? 0 }}</p>
    </div>
</div>

<h3 style="margin-top: 2rem;">Hasil HPE Terbaru</h3>
<div id="recent-hpe" style="margin-top: 1rem;">
    @if(isset($recent_hpe_results) && count($recent_hpe_results) > 0)
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Total (IDR)</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recent_hpe_results as $result)
                <tr>
                    <td>{{ $result['product_name'] }}</td>
                    <td>{{ number_format($result['total_with_margin'], 0, ',', '.') }}</td>
                    <td>{{ $result['calculated_at'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Tidak ada data HPE</p>
    @endif
</div>

@push('scripts')
<script>
    // Dashboard data already loaded from server-side
    // This script can refresh data via API if needed
    const token = localStorage.getItem('api_token') || '';
    if (!token && window.location.pathname !== '/login') {
        window.location.href = '/login';
    }
</script>
@endpush
@endsection

