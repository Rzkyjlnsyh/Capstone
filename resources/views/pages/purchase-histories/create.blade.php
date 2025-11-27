@extends('layouts.app')

@section('title', 'Tambah Riwayat Pengadaan')

@section('content')
<h2>Tambah Riwayat Pengadaan</h2>

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
        <input type="date" name="purchase_date" required>
    </div>
    <div class="form-group">
        <label>Vendor</label>
        <input type="text" name="vendor_name" placeholder="Nama vendor">
    </div>
    <div class="form-group">
        <label>Mata Uang *</label>
        <select name="currency" id="currency-select" required>
            <option value="USD">USD</option>
            <option value="IDR" selected>IDR</option>
        </select>
    </div>
    <div class="form-group" id="rate-group">
        <label>Kurs USD/IDR (jika USD) *</label>
        <select name="exchange_rate_id" id="rate-select">
            <option value="">Pilih Kurs...</option>
        </select>
        <small>atau</small>
        <input type="number" name="rate_value_snapshot" step="0.01" placeholder="Masukkan kurs manual">
    </div>
    <div class="form-group">
        <label>Harga Satuan (Original) *</label>
        <input type="number" name="unit_price_original" step="0.01" min="0" required placeholder="0.00">
    </div>
    <div class="form-group">
        <label>Kuantitas</label>
        <input type="number" name="quantity" step="0.001" min="0.001" value="1">
    </div>
    <div class="form-group">
        <label>Catatan</label>
        <textarea name="notes" rows="3"></textarea>
    </div>
    <div style="margin-top: 1.5rem;">
        <button type="submit" class="btn">Simpan</button>
        <a href="/purchase-histories" class="btn btn-secondary" style="margin-left: 0.5rem;">Batal</a>
    </div>
</form>

@push('scripts')
<script>
let apiToken = localStorage.getItem('api_token') || '';

function loadProducts() {
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
                select.innerHTML += `<option value="${prod.id}">${prod.name} (${prod.code})</option>`;
            });
        }
    });
}

function loadComponents() {
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
                select.innerHTML += `<option value="${comp.id}">${comp.name} (${comp.code})</option>`;
            });
        }
    });
}

function loadExchangeRates() {
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
                select.innerHTML += `<option value="${rate.id}">${rate.rate_date} - ${rate.rate_value}</option>`;
            });
        }
    });
}

document.getElementById('currency-select').addEventListener('change', function(e) {
    document.getElementById('rate-group').style.display = e.target.value === 'USD' ? 'block' : 'none';
});

document.getElementById('purchase-history-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    // Set today as default date if empty
    if (!data.purchase_date) {
        data.purchase_date = new Date().toISOString().split('T')[0];
    }
    
    fetch('/api/purchase-histories', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + apiToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
        if (data.id) {
            alert('Riwayat pengadaan berhasil ditambahkan!');
            window.location.href = '/purchase-histories';
        } else {
            alert('Error: ' + (data.message || 'Gagal menambahkan riwayat pengadaan'));
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Gagal menambahkan riwayat pengadaan');
    });
});

loadProducts();
loadComponents();
loadExchangeRates();
</script>
@endpush
@endsection

