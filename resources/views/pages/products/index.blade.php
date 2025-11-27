@extends('layouts.app')

@section('title', 'Daftar Produk')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Daftar Produk</h2>
    <a href="{{ url('/products/create') }}" class="btn">+ Tambah Produk</a>
</div>

<div style="margin-bottom: 1rem;">
    <input type="text" id="search-input" placeholder="Cari produk..." style="padding: 0.5rem; width: 300px; border: 1px solid #d1d5db; border-radius: 4px;">
    <select id="status-filter" style="padding: 0.5rem; margin-left: 0.5rem; border: 1px solid #d1d5db; border-radius: 4px;">
        <option value="">Semua Status</option>
        <option value="active">Aktif</option>
        <option value="inactive">Tidak Aktif</option>
    </select>
</div>

<div id="products-table-container">
    <p>Memuat data...</p>
</div>

@push('scripts')
<script>
let apiToken = localStorage.getItem('api_token') || '';

function loadProducts(search = '', status = '') {
    const container = document.getElementById('products-table-container');
    let url = '/api/products?per_page=20';
    if (search) url += '&search=' + encodeURIComponent(search);
    if (status) url += '&status=' + status;

    fetch(url, {
        headers: {
            'Authorization': 'Bearer ' + apiToken,
            'Accept': 'application/json'
        }
    })
    .then(res => {
        if (res.status === 401) {
            container.innerHTML = '<div class="alert alert-error">Silakan login terlebih dahulu. <a href="/login">Login</a></div>';
            return;
        }
        return res.json();
    })
    .then(data => {
        if (!data) return;
        
        if (data.data && data.data.length > 0) {
            let html = '<table><thead><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Status</th><th>Komponen</th><th>Aksi</th></tr></thead><tbody>';
            data.data.forEach(product => {
                html += `<tr>
                    <td>${product.code || '-'}</td>
                    <td>${product.name || '-'}</td>
                    <td>${product.category || '-'}</td>
                    <td><span style="padding: 0.25rem 0.5rem; border-radius: 4px; background: ${product.status === 'active' ? '#d1fae5' : '#fee2e2'}; color: ${product.status === 'active' ? '#065f46' : '#991b1b'};">${product.status || '-'}</span></td>
                    <td>${product.product_components ? product.product_components.length : 0} komponen</td>
                    <td>
                        <a href="/products/${product.id}" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">Detail</a>
                        <a href="/products/${product.id}/edit" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">Edit</a>
                    </td>
                </tr>`;
            });
            html += '</tbody></table>';
            
            if (data.links) {
                html += '<div style="margin-top: 1rem;">';
                if (data.links.prev) html += `<a href="#" onclick="loadPage('${data.links.prev}'); return false;">&laquo; Sebelumnya</a> `;
                if (data.links.next) html += `<a href="#" onclick="loadPage('${data.links.next}'); return false;">Selanjutnya &raquo;</a>`;
                html += '</div>';
            }
            
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p>Tidak ada produk ditemukan.</p>';
        }
    })
    .catch(err => {
        console.error('Error:', err);
        container.innerHTML = '<div class="alert alert-error">Gagal memuat data produk.</div>';
    });
}

function loadPage(url) {
    fetch(url, {
        headers: {
            'Authorization': 'Bearer ' + apiToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        // Similar rendering logic
        loadProducts();
    });
}

document.getElementById('search-input').addEventListener('input', function(e) {
    const status = document.getElementById('status-filter').value;
    loadProducts(e.target.value, status);
});

document.getElementById('status-filter').addEventListener('change', function(e) {
    const search = document.getElementById('search-input').value;
    loadProducts(search, e.target.value);
});

// Initial load
loadProducts();
</script>
@endpush
@endsection

