@extends('layouts.app')

@section('title', 'Laporan & Export')

@section('content')
<h2>Laporan & Export</h2>

<div style="margin-top: 2rem;">
    <h3>Export HPE Results</h3>
    <form id="export-hpe-form" style="max-width: 600px; margin-top: 1rem;">
        <div class="form-group">
            <label>Format *</label>
            <select name="type" required>
                <option value="pdf">PDF</option>
                <option value="excel">Excel</option>
            </select>
        </div>
        <div class="form-group">
            <label>Tanggal Dari (opsional)</label>
            <input type="date" name="date_from">
        </div>
        <div class="form-group">
            <label>Tanggal Sampai (opsional)</label>
            <input type="date" name="date_to">
        </div>
        <div class="form-group">
            <label>Produk (opsional)</label>
            <select name="product_id" id="product-select">
                <option value="">Semua Produk</option>
            </select>
        </div>
        <div style="margin-top: 1rem;">
            <button type="submit" class="btn">Export</button>
        </div>
    </form>
</div>

<div style="margin-top: 3rem;">
    <h3>Export Produk & Komponen</h3>
    <form id="export-products-form" style="max-width: 600px; margin-top: 1rem;">
        <div class="form-group">
            <label>Format *</label>
            <select name="type" required>
                <option value="pdf">PDF</option>
                <option value="excel">Excel</option>
            </select>
        </div>
        <div style="margin-top: 1rem;">
            <button type="submit" class="btn">Export</button>
        </div>
    </form>
</div>

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

document.getElementById('export-hpe-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const params = new URLSearchParams(formData);
    window.open(`/api/reporting/export-hpe?${params.toString()}`, '_blank');
});

document.getElementById('export-products-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const params = new URLSearchParams(formData);
    window.open(`/api/reporting/export-products?${params.toString()}`, '_blank');
});

loadProducts();
</script>
@endpush
@endsection

