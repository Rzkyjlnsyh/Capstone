@extends('layouts.app')

@section('title', 'Riwayat Pengadaan')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Riwayat Pengadaan</h2>
    <a href="/purchase-histories/create" class="btn">+ Tambah Transaksi</a>
</div>

<div id="purchase-histories-container">
    <p>Memuat data...</p>
</div>

@push('scripts')
<script>
let apiToken = localStorage.getItem('api_token') || '';

function loadPurchaseHistories() {
    fetch('/api/purchase-histories?per_page=20', {
        headers: {
            'Authorization': 'Bearer ' + apiToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById('purchase-histories-container');
        if (data.data && data.data.length > 0) {
            let html = '<table><thead><tr><th>Tanggal</th><th>Produk</th><th>Komponen</th><th>Vendor</th><th>Harga (IDR)</th><th>Kurs</th><th>Aksi</th></tr></thead><tbody>';
            data.data.forEach(ph => {
                html += `<tr>
                    <td>${ph.purchase_date || '-'}</td>
                    <td>${ph.product ? ph.product.name : '-'}</td>
                    <td>${ph.component ? ph.component.name : '-'}</td>
                    <td>${ph.vendor_name || '-'}</td>
                    <td>${ph.unit_price_idr ? new Intl.NumberFormat('id-ID').format(ph.unit_price_idr) : '-'}</td>
                    <td>${ph.exchange_rate ? ph.exchange_rate.rate_value : '-'}</td>
                    <td>
                        <a href="/purchase-histories/${ph.id}/edit" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">Edit</a>
                    </td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p>Tidak ada riwayat pengadaan ditemukan.</p>';
        }
    })
    .catch(err => {
        console.error('Error:', err);
        document.getElementById('purchase-histories-container').innerHTML = '<div class="alert alert-error">Gagal memuat data.</div>';
    });
}

loadPurchaseHistories();
</script>
@endpush
@endsection

