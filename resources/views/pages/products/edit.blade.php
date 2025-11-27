@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content')
<div id="product-edit">
    <p>Memuat data...</p>
</div>

@push('scripts')
<script>
let apiToken = localStorage.getItem('api_token') || '';
let productId = window.location.pathname.split('/')[2];

function loadProduct() {
    fetch(`/api/products/${productId}`, {
        headers: {
            'Authorization': 'Bearer ' + apiToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.id) {
            document.getElementById('product-edit').innerHTML = `
                <h2>Edit Produk</h2>
                <form id="product-form" style="max-width: 600px; margin-top: 2rem;">
                    <div class="form-group">
                        <label>Kode Produk *</label>
                        <input type="text" name="code" value="${data.code || ''}" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Produk *</label>
                        <input type="text" name="name" value="${data.name || ''}" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="description" rows="3">${data.description || ''}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <input type="text" name="category" value="${data.category || ''}">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="active" ${data.status === 'active' ? 'selected' : ''}>Aktif</option>
                            <option value="inactive" ${data.status === 'inactive' ? 'selected' : ''}>Tidak Aktif</option>
                        </select>
                    </div>
                    <div style="margin-top: 1.5rem;">
                        <button type="submit" class="btn">Simpan Perubahan</button>
                        <a href="/products/${data.id}" class="btn btn-secondary" style="margin-left: 0.5rem;">Batal</a>
                    </div>
                </form>
            `;
            
            document.getElementById('product-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const updateData = Object.fromEntries(formData);
                
                fetch(`/api/products/${productId}`, {
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
                        alert('Produk berhasil diperbarui!');
                        window.location.href = '/products/' + data.id;
                    } else {
                        alert('Error: ' + (data.message || 'Gagal memperbarui produk'));
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('Gagal memperbarui produk');
                });
            });
        }
    })
    .catch(err => {
        console.error('Error:', err);
        document.getElementById('product-edit').innerHTML = '<div class="alert alert-error">Gagal memuat data produk.</div>';
    });
}

loadProduct();
</script>
@endpush
@endsection

