@extends('layouts.app')

@section('title', 'Hasil HPE')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Hasil Perhitungan HPE</h2>
    <a href="/hpe/calculate" class="btn">+ Hitung HPE Baru</a>
</div>

<div id="hpe-results-container">
    <p>Memuat data...</p>
</div>

@push('scripts')
<script>
let apiToken = localStorage.getItem('api_token') || '';

function loadHpeResults() {
    fetch('/api/hpe/results?per_page=20', {
        headers: {
            'Authorization': 'Bearer ' + apiToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById('hpe-results-container');
        if (data.data && data.data.length > 0) {
            let html = '<table><thead><tr><th>Tanggal</th><th>Produk</th><th>Total Biaya</th><th>Margin</th><th>Total + Margin</th><th>Status</th><th>Aksi</th></tr></thead><tbody>';
            data.data.forEach(result => {
                html += `<tr>
                    <td>${result.calculated_at ? new Date(result.calculated_at).toLocaleDateString('id-ID') : '-'}</td>
                    <td>${result.product ? result.product.name : '-'}</td>
                    <td>${result.total_cost_idr ? new Intl.NumberFormat('id-ID').format(result.total_cost_idr) : '-'}</td>
                    <td>${result.margin_percent || 0}%</td>
                    <td><strong>${result.total_with_margin ? new Intl.NumberFormat('id-ID').format(result.total_with_margin) : '-'}</strong></td>
                    <td>${result.status || '-'}</td>
                    <td><a href="/hpe/results/${result.id}" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">Detail</a></td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p>Tidak ada hasil HPE ditemukan.</p>';
        }
    })
    .catch(err => {
        console.error('Error:', err);
        document.getElementById('hpe-results-container').innerHTML = '<div class="alert alert-error">Gagal memuat data.</div>';
    });
}

loadHpeResults();
</script>
@endpush
@endsection

