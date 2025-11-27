@extends('layouts.app')

@section('title', 'Tambah Komponen')

@section('content')
<h2>Tambah Komponen Baru</h2>

<form id="component-form" style="max-width: 600px; margin-top: 2rem;">
    <div class="form-group">
        <label>Kode Komponen *</label>
        <input type="text" name="code" required placeholder="Contoh: CMP-001">
    </div>
    <div class="form-group">
        <label>Nama Komponen *</label>
        <input type="text" name="name" required>
    </div>
    <div class="form-group">
        <label>Unit *</label>
        <input type="text" name="unit" required placeholder="kg, liter, pcs, dll">
    </div>
    <div class="form-group">
        <label>Deskripsi</label>
        <textarea name="description" rows="3"></textarea>
    </div>
    <div style="margin-top: 1.5rem;">
        <button type="submit" class="btn">Simpan</button>
        <a href="/components" class="btn btn-secondary" style="margin-left: 0.5rem;">Batal</a>
    </div>
</form>

@push('scripts')
<script>
let apiToken = localStorage.getItem('api_token') || '';

document.getElementById('component-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    fetch('/api/components', {
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
            alert('Komponen berhasil ditambahkan!');
            window.location.href = '/components';
        } else {
            alert('Error: ' + (data.message || 'Gagal menambahkan komponen'));
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Gagal menambahkan komponen');
    });
});
</script>
@endpush
@endsection

