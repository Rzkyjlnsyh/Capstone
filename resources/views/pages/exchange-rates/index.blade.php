@extends('layouts.app')

@section('title', 'Kurs JISDOR')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Kurs JISDOR (USD/IDR)</h2>
    <button onclick="syncRates()" class="btn">Sinkronisasi Kurs</button>
</div>

<div id="exchange-rates-container">
    <p>Memuat data...</p>
</div>

@push('scripts')
<script>
let apiToken = localStorage.getItem('api_token') || '';

function loadExchangeRates() {
    fetch('/api/exchange-rates?per_page=30', {
        headers: {
            'Authorization': 'Bearer ' + apiToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById('exchange-rates-container');
        if (data.data && data.data.length > 0) {
            let html = '<table><thead><tr><th>Tanggal</th><th>Kurs USD/IDR</th><th>Sumber</th><th>Diperbarui</th></tr></thead><tbody>';
            data.data.forEach(rate => {
                html += `<tr>
                    <td>${rate.rate_date || '-'}</td>
                    <td><strong>${new Intl.NumberFormat('id-ID').format(rate.rate_value)}</strong></td>
                    <td>${rate.source || '-'}</td>
                    <td>${rate.fetched_at ? new Date(rate.fetched_at).toLocaleString('id-ID') : '-'}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p>Tidak ada data kurs ditemukan.</p>';
        }
    })
    .catch(err => {
        console.error('Error:', err);
        document.getElementById('exchange-rates-container').innerHTML = '<div class="alert alert-error">Gagal memuat data.</div>';
    });
}

function syncRates() {
    if (!confirm('Sinkronisasi kurs untuk hari ini?')) return;
    
    fetch('/api/exchange-rates/sync', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + apiToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ date: new Date().toISOString().split('T')[0] })
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message || 'Sinkronisasi berhasil!');
        loadExchangeRates();
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Gagal sinkronisasi kurs');
    });
}

loadExchangeRates();
</script>
@endpush
@endsection

