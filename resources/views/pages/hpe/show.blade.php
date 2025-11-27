@extends('layouts.app')

@section('title', 'Detail Hasil HPE')

@section('content')
<div id="hpe-detail">
    <p>Memuat data...</p>
</div>

@push('scripts')
<script>
let apiToken = localStorage.getItem('api_token') || '';
let hpeId = window.location.pathname.split('/').pop();

function loadHpeDetail() {
    fetch(`/api/hpe/results/${hpeId}`, {
        headers: {
            'Authorization': 'Bearer ' + apiToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.id) {
            let html = `<h2>Detail Hasil HPE</h2>
                <div style="margin-top: 1.5rem;">
                    <p><strong>Produk:</strong> ${data.product ? data.product.name : '-'} (${data.product ? data.product.code : '-'})</p>
                    <p><strong>Tanggal Perhitungan:</strong> ${data.calculated_at ? new Date(data.calculated_at).toLocaleString('id-ID') : '-'}</p>
                    <p><strong>Total Biaya:</strong> ${new Intl.NumberFormat('id-ID').format(data.total_cost_idr)} IDR</p>
                    <p><strong>Margin:</strong> ${data.margin_percent}%</p>
                    <p><strong>Total dengan Margin:</strong> <strong style="font-size: 1.2rem;">${new Intl.NumberFormat('id-ID').format(data.total_with_margin)} IDR</strong></p>
                    <p><strong>Kurs USD/IDR:</strong> ${data.exchange_rate ? data.exchange_rate.rate_value : '-'}</p>
                </div>`;
            
            if (data.component_breakdown && data.component_breakdown.length > 0) {
                html += '<h3 style="margin-top: 2rem;">Breakdown Komponen:</h3><table style="margin-top: 1rem;"><thead><tr><th>Komponen</th><th>Qty</th><th>Rata-rata</th><th>Subtotal</th></tr></thead><tbody>';
                data.component_breakdown.forEach(comp => {
                    html += `<tr>
                        <td>${comp.component_name || '-'}</td>
                        <td>${comp.quantity || '-'} ${comp.unit || ''}</td>
                        <td>${comp.average_price ? new Intl.NumberFormat('id-ID').format(comp.average_price) : '-'}</td>
                        <td>${comp.subtotal ? new Intl.NumberFormat('id-ID').format(comp.subtotal) : '-'}</td>
                    </tr>`;
                });
                html += '</tbody></table>';
            }
            
            if (data.warnings && data.warnings.length > 0) {
                html += '<div class="alert alert-error" style="margin-top: 1rem;"><strong>Peringatan:</strong><ul>';
                data.warnings.forEach(w => {
                    html += `<li>${w}</li>`;
                });
                html += '</ul></div>';
            }
            
            html += `<div style="margin-top: 1.5rem;">
                <a href="/hpe/results" class="btn">Kembali ke Daftar</a>
                <a href="/reporting/export-hpe?type=pdf&hpe_result_id=${data.id}" class="btn" style="margin-left: 0.5rem;">Export PDF</a>
                <a href="/reporting/export-hpe?type=excel&hpe_result_id=${data.id}" class="btn" style="margin-left: 0.5rem;">Export Excel</a>
            </div>`;
            
            document.getElementById('hpe-detail').innerHTML = html;
        }
    })
    .catch(err => {
        console.error('Error:', err);
        document.getElementById('hpe-detail').innerHTML = '<div class="alert alert-error">Gagal memuat data.</div>';
    });
}

loadHpeDetail();
</script>
@endpush
@endsection

