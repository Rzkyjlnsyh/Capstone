@extends('layouts.app')

@section('title', 'Detail Produk')

@section('content')
<div id="product-detail">
    <p>Memuat data...</p>
</div>

<div id="bom-section" style="margin-top: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3>Komponen Penyusun (BoM)</h3>
        <button onclick="showAddComponentModal()" class="btn">+ Tambah Komponen</button>
    </div>
    <div id="bom-list">
        <p>Memuat komponen...</p>
    </div>
</div>

<!-- Modal Add Component -->
<div id="add-component-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 500px; width: 90%;">
        <h3>Tambah Komponen ke Produk</h3>
        <form id="add-component-form" style="margin-top: 1rem;">
            <div class="form-group">
                <label>Komponen *</label>
                <select name="component_id" id="component-select" required>
                    <option value="">Pilih Komponen...</option>
                </select>
            </div>
            <div class="form-group">
                <label>Kuantitas *</label>
                <input type="number" name="quantity" step="0.001" min="0.001" required placeholder="0.000">
            </div>
            <div class="form-group">
                <label>Unit Override (opsional)</label>
                <input type="text" name="unit_override" placeholder="Override unit default">
            </div>
            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn">Tambah</button>
                <button type="button" onclick="hideAddComponentModal()" class="btn btn-secondary" style="margin-left: 0.5rem;">Batal</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let apiToken = localStorage.getItem('api_token') || '';
let productId = window.location.pathname.split('/').pop();

function loadProduct() {
    fetch(`/api/products/${productId}?with_components=1`, {
        headers: {
            'Authorization': 'Bearer ' + apiToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.id) {
            document.getElementById('product-detail').innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <h2>${data.name || '-'}</h2>
                        <p><strong>Kode:</strong> ${data.code || '-'}</p>
                        <p><strong>Kategori:</strong> ${data.category || '-'}</p>
                        <p><strong>Status:</strong> <span style="padding: 0.25rem 0.5rem; border-radius: 4px; background: ${data.status === 'active' ? '#d1fae5' : '#fee2e2'}; color: ${data.status === 'active' ? '#065f46' : '#991b1b'};">${data.status || '-'}</span></p>
                        <p><strong>Deskripsi:</strong> ${data.description || '-'}</p>
                    </div>
                    <div>
                        <a href="/products/${data.id}/edit" class="btn">Edit</a>
                        <a href="/products" class="btn btn-secondary" style="margin-left: 0.5rem;">Kembali</a>
                    </div>
                </div>
            `;
            
            if (data.product_components && data.product_components.length > 0) {
                let bomHtml = '<table><thead><tr><th>Komponen</th><th>Kuantitas</th><th>Unit</th><th>Aksi</th></tr></thead><tbody>';
                data.product_components.forEach(pc => {
                    bomHtml += `<tr>
                        <td>${pc.component ? pc.component.name : '-'}</td>
                        <td>${pc.quantity || '-'}</td>
                        <td>${pc.unit_override || (pc.component ? pc.component.unit : '-')}</td>
                        <td>
                            <button onclick="editComponent(${pc.id}, ${pc.component_id}, ${pc.quantity}, '${pc.unit_override || ''}')" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">Edit</button>
                            <button onclick="deleteComponent(${pc.id})" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">Hapus</button>
                        </td>
                    </tr>`;
                });
                bomHtml += '</tbody></table>';
                document.getElementById('bom-list').innerHTML = bomHtml;
            } else {
                document.getElementById('bom-list').innerHTML = '<p>Belum ada komponen. Klik "Tambah Komponen" untuk menambahkan.</p>';
            }
        }
    })
    .catch(err => {
        console.error('Error:', err);
        document.getElementById('product-detail').innerHTML = '<div class="alert alert-error">Gagal memuat data produk.</div>';
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
        select.innerHTML = '<option value="">Pilih Komponen...</option>';
        if (data.data) {
            data.data.forEach(comp => {
                select.innerHTML += `<option value="${comp.id}">${comp.name} (${comp.code})</option>`;
            });
        }
    });
}

function showAddComponentModal() {
    loadComponents();
    document.getElementById('add-component-modal').style.display = 'flex';
}

function hideAddComponentModal() {
    document.getElementById('add-component-modal').style.display = 'none';
    document.getElementById('add-component-form').reset();
}

document.getElementById('add-component-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    data.quantity = parseFloat(data.quantity);
    
    fetch(`/api/products/${productId}/components`, {
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
        if (data.data) {
            alert('Komponen berhasil ditambahkan!');
            hideAddComponentModal();
            loadProduct();
        } else {
            alert('Error: ' + (data.message || 'Gagal menambahkan komponen'));
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Gagal menambahkan komponen');
    });
});

function deleteComponent(pcId) {
    if (!confirm('Yakin ingin menghapus komponen ini?')) return;
    
    fetch(`/api/products/${productId}/components/${pcId}`, {
        method: 'DELETE',
        headers: {
            'Authorization': 'Bearer ' + apiToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        alert('Komponen berhasil dihapus!');
        loadProduct();
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Gagal menghapus komponen');
    });
}

loadProduct();
</script>
@endpush
@endsection

