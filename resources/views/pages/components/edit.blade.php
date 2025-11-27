@extends('layouts.app')

@section('title', 'Edit Komponen')

@section('content')
<div id="component-edit">
    <p>Memuat data...</p>
</div>

@push('scripts')
<script>
let apiToken = localStorage.getItem('api_token') || '';
let componentId = window.location.pathname.split('/')[2];

function loadComponent() {
    fetch(`/api/components/${componentId}`, {
        headers: {
            'Authorization': 'Bearer ' + apiToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.id) {
            document.getElementById('component-edit').innerHTML = `
                <h2>Edit Komponen</h2>
                <form id="component-form" style="max-width: 600px; margin-top: 2rem;">
                    <div class="form-group">
                        <label>Kode Komponen *</label>
                        <input type="text" name="code" value="${data.code || ''}" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Komponen *</label>
                        <input type="text" name="name" value="${data.name || ''}" required>
                    </div>
                    <div class="form-group">
                        <label>Unit *</label>
                        <input type="text" name="unit" value="${data.unit || ''}" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="description" rows="3">${data.description || ''}</textarea>
                    </div>
                    <div style="margin-top: 1.5rem;">
                        <button type="submit" class="btn">Simpan Perubahan</button>
                        <a href="/components" class="btn btn-secondary" style="margin-left: 0.5rem;">Batal</a>
                    </div>
                </form>
            `;
            
            document.getElementById('component-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const updateData = Object.fromEntries(formData);
                
                fetch(`/api/components/${componentId}`, {
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
                        alert('Komponen berhasil diperbarui!');
                        window.location.href = '/components';
                    } else {
                        alert('Error: ' + (data.message || 'Gagal memperbarui komponen'));
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('Gagal memperbarui komponen');
                });
            });
        }
    })
    .catch(err => {
        console.error('Error:', err);
        document.getElementById('component-edit').innerHTML = '<div class="alert alert-error">Gagal memuat data komponen.</div>';
    });
}

loadComponent();
</script>
@endpush
@endsection

