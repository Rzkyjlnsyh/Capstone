@extends('layouts.app')

@section('title', 'Edit Riwayat Pengadaan')

@section('content')
<div id="purchase-history-edit">
    <p>Memuat data...</p>
</div>

@push('scripts')
<script>
let apiToken = localStorage.getItem('api_token') || '';
let phId = window.location.pathname.split('/')[2];

function loadPurchaseHistory() {
    fetch(`/api/purchase-histories/${phId}`, {
        headers: {
            'Authorization': 'Bearer ' + apiToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.id) {
            document.getElementById('purchase-history-edit').innerHTML = `
                <h2>Edit Riwayat Pengadaan</h2>
                <form id="purchase-history-form" style="max-width: 600px; margin-top: 2rem;">
                    <div class="form-group">
                        <label>Produk *</label>
                        <select name="product_id" id="product-select" required>
                            <option value="">Pilih Produk...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Komponen *</label>
                        <select name="component_id" id="component-select" required>
                            <option value="">Pilih Komponen...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Pengadaan *</label>
                        <input type="date" name="purchase_date" value="${data.purchase_date || ''}" required>
                    </div>
                    <div class="form-group">
                        <label>Vendor</label>
                        <input type="text" name="vendor_name" value="${data.vendor_name || ''}" placeholder="Nama vendor">
                    </div>
                    <div class="form-group">
                        <label>Mata Uang *</label>
                        <select name="currency" id="currency-select" required>
                            <option value="USD" ${data.currency === 'USD' ? 'selected' : ''}>USD</option>
                            <option value="IDR" ${data.currency === 'IDR' ? 'selected' : ''}>IDR</option>
                        </select>
                    </div>
                    <div class="form-group" id="rate-group" style="display: ${data.currency === 'USD' ? 'block' : 'none'};">
                        <label>Kurs USD/IDR</label>
                        <select name="exchange_rate_id" id="rate-select">
                            <option value="">Pilih Kurs...</option>
                        </select>
                        <input type="number" name="rate_value_snapshot" step="0.01" value="${data.rate_value_snapshot || ''}" placeholder="Kurs manual">
                    </div>
                    <div class="form-group">
                        <label>Harga Satuan (Original) *</label>
                        <input type="number" name="unit_price_original" step="0.01" min="0" value="${data.unit_price_original || ''}" required>
                    </div>
                    <div class="form-group">
                        <label>Kuantitas</label>
                        <input type="number" name="quantity" step="0.001" min="0.001" value="${data.quantity || 1}">
                    </div>
                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea name="notes" rows="3">${data.notes || ''}</textarea>
                    </div>
                    <div style="margin-top: 1.5rem;">
                        <button type="submit" class="btn">Simpan Perubahan</button>
                        <a href="/purchase-histories" class="btn btn-secondary" style="margin-left: 0.5rem;">Batal</a>
                    </div>
                </form>
            `;
            
            // Load dropdowns
            loadProducts(data.product_id);
            loadComponents(data.component_id);
            loadExchangeRates(data.exchange_rate_id);
            
            document.getElementById('currency-select').addEventListener('change', function(e) {
                document.getElementById('rate-group').style.display = e.target.value === 'USD' ? 'block' : 'none';
            });
            
            document.getElementById('purchase-history-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const updateData = Object.fromEntries(formData);
                
                fetch(`/api/purchase-histories/${phId}`, {
                    method: 'PUT',
                    headers: {
                        'Authorization': 'Bearer ' + apiToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(updateData)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.id) {
                        alert('Riwayat pengadaan berhasil diperbarui!');
                        window.location.href = '/purchase-histories';
                    } else {
                        alert('Error: ' + (data.message || 'Gagal memperbarui'));
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('Gagal memperbarui riwayat pengadaan');
                });
            });
        }
    })
    .catch(err => {
        console.error('Error:', err);
        document.getElementById('purchase-history-edit').innerHTML = '<div class="alert alert-error">Gagal memuat data.</div>';
    });
}

function loadProducts(selectedId = null) {
    fetch('/api/products', {
        headers: {
            'Authorization': 'Bearer ' + apiToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        const select = document.getElementById('product-select');
        if (data.data) {
            data.data.forEach(prod => {
                select.innerHTML += `<option value="${prod.id}" ${prod.id == selectedId ? 'selected' : ''}>${prod.name} (${prod.code})</option>`;
            });
        }
    });
}

function loadComponents(selectedId = null) {
    fetch('/api/components', {
        headers: {
            'Authorization': 'Bearer ' + apiToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        const select = document.getElementById('component-select');
        if (data.data) {
            data.data.forEach(comp => {
                select.innerHTML += `<option value="${comp.id}" ${comp.id == selectedId ? 'selected' : ''}>${comp.name} (${comp.code})</option>`;
            });
        }
    });
}

function loadExchangeRates(selectedId = null) {
    fetch('/api/exchange-rates?per_page=10', {
        headers: {
            'Authorization': 'Bearer ' + apiToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        const select = document.getElementById('rate-select');
        if (data.data) {
            data.data.forEach(rate => {
                select.innerHTML += `<option value="${rate.id}" ${rate.id == selectedId ? 'selected' : ''}>${rate.rate_date} - ${rate.rate_value}</option>`;
            });
        }
    });
}

loadPurchaseHistory();
</script>
@endpush
@endsection

