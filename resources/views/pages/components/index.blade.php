@extends('layouts.app')

@section('title', 'Daftar Komponen')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Daftar Komponen</h2>
    <a href="/components/create" class="btn">+ Tambah Komponen</a>
</div>

<div id="components-table-container">
    <p>Memuat data...</p>
</div>

@push('scripts')
<script>
let apiToken = localStorage.getItem('api_token') || '';

function loadComponents() {
    fetch('/api/components', {
        headers: {
            'Authorization': 'Bearer ' + apiToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById('components-table-container');
        if (data.data && data.data.length > 0) {
            let html = '<table><thead><tr><th>Kode</th><th>Nama</th><th>Unit</th><th>Deskripsi</th><th>Aksi</th></tr></thead><tbody>';
            data.data.forEach(comp => {
                html += `<tr>
                    <td>${comp.code || '-'}</td>
                    <td>${comp.name || '-'}</td>
                    <td>${comp.unit || '-'}</td>
                    <td>${comp.description || '-'}</td>
                    <td>
                        <a href="/components/${comp.id}/edit" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">Edit</a>
                    </td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p>Tidak ada komponen ditemukan.</p>';
        }
    })
    .catch(err => {
        console.error('Error:', err);
        document.getElementById('components-table-container').innerHTML = '<div class="alert alert-error">Gagal memuat data komponen.</div>';
    });
}

loadComponents();
</script>
@endpush
@endsection

