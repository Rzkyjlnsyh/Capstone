@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('content')
<h2>Tambah Produk Baru</h2>

<form id="product-form" style="max-width: 600px; margin-top: 2rem;">
    <div class="form-group">
        <label>Kode Produk *</label>
        <input type="text" name="code" required placeholder="Contoh: PRD-001">
    </div>
    
    <div class="form-group">
        <label>Nama Produk *</label>
        <input type="text" name="name" required placeholder="Nama produk">
    </div>
    
    <div class="form-group">
        <label>Deskripsi</label>
        <textarea name="description" rows="3" placeholder="Deskripsi singkat produk"></textarea>
    </div>
    
    <div class="form-group">
        <label>Kategori</label>
        <input type="text" name="category" placeholder="Kategori produk">
    </div>
    
    <div class="form-group">
        <label>Status</label>
        <select name="status">
            <option value="active">Aktif</option>
            <option value="inactive">Tidak Aktif</option>
        </select>
    </div>
    
    <div style="margin-top: 1.5rem;">
        <button type="submit" class="btn">Simpan</button>
        <a href="{{ url('/products') }}" class="btn btn-secondary" style="margin-left: 0.5rem;">Batal</a>
    </div>
</form>

@push('scripts')
<script>
let apiToken = localStorage.getItem('api_token') || '';

document.getElementById('product-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    fetch('/api/products', {
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
            alert('Produk berhasil ditambahkan!');
            window.location.href = '/products/' + data.id;
        } else {
            alert('Error: ' + (data.message || 'Gagal menambahkan produk'));
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Gagal menambahkan produk');
    });
});
</script>
@endpush
@endsection

