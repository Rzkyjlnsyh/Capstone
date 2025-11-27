@extends('layouts.app')

@section('title', 'Hitung HPE')

@section('content')
<h2>Hitung Harga Perkiraan Estimasi (HPE)</h2>

<form id="calculate-hpe-form" style="max-width: 600px; margin-top: 2rem;">
    <div class="form-group">
        <label>Produk *</label>
        <select name="product_id" id="product-select" required>
            <option value="">Pilih Produk...</option>
        </select>
    </div>
    <div class="form-group">
        <label>Margin (%)</label>
        <input type="number" name="margin_percent" step="0.1" min="0" max="100" value="10" placeholder="10">
        <small>Default: 10%</small>
    </div>
    <div style="margin-top: 1.5rem;">
        <button type="submit" class="btn">Hitung HPE</button>
        <a href="/hpe/results" class="btn btn-secondary" style="margin-left: 0.5rem;">Batal</a>
    </div>
</form>

<div id="result-container" style="margin-top: 2rem; display: none;"></div>

@push('scripts')
<script>
let apiToken = localStorage.getItem('api_token') || '';

function loadProducts() {
    fetch('/api/products?with_components=1', {
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
                const compCount = prod.product_components ? prod.product_components.length : 0;
                select.innerHTML += `<option value="${prod.id}">${prod.name} (${prod.code}) - ${compCount} komponen</option>`;
            });
        }
    });
}

document.getElementById('calculate-hpe-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    data.margin_percent = parseFloat(data.margin_percent) || 0;
    
    const container = document.getElementById('result-container');
    container.style.display = 'block';
    container.innerHTML = '<p>Menghitung HPE...</p>';
    
    fetch('/api/hpe/calculate', {
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
            let html = '<div class="alert alert-success"><h3>HPE Berhasil Dihitung!</h3></div>';
            html += `<div style="margin-top: 1rem;">
                <p><strong>Produk:</strong> ${data.data.product_name}</p>
                <p><strong>Total Biaya:</strong> ${new Intl.NumberFormat('id-ID').format(data.data.total_cost_idr)} IDR</p>
                <p><strong>Margin:</strong> ${data.data.margin_percent}%</p>
                <p><strong>Total dengan Margin:</strong> <strong style="font-size: 1.2rem;">${new Intl.NumberFormat('id-ID').format(data.data.total_with_margin)} IDR</strong></p>
            </div>`;
            
            if (data.data.component_breakdown && data.data.component_breakdown.length > 0) {
                html += '<h4 style="margin-top: 1.5rem;">Breakdown Komponen:</h4><table><thead><tr><th>Komponen</th><th>Qty</th><th>Rata-rata Harga</th><th>Subtotal</th></tr></thead><tbody>';
                data.data.component_breakdown.forEach(comp => {
                    html += `<tr>
                        <td>${comp.component_name || '-'}</td>
                        <td>${comp.quantity || '-'} ${comp.unit || ''}</td>
                        <td>${comp.average_price ? new Intl.NumberFormat('id-ID').format(comp.average_price) : '-'}</td>
                        <td>${comp.subtotal ? new Intl.NumberFormat('id-ID').format(comp.subtotal) : '-'}</td>
                    </tr>`;
                });
                html += '</tbody></table>';
            }
            
            if (data.data.warnings && data.data.warnings.length > 0) {
                html += '<div class="alert alert-error" style="margin-top: 1rem;"><strong>Peringatan:</strong><ul>';
                data.data.warnings.forEach(w => {
                    html += `<li>${w}</li>`;
                });
                html += '</ul></div>';
            }
            
            html += `<div style="margin-top: 1.5rem;">
                <a href="/hpe/results/${data.data.id}" class="btn">Lihat Detail</a>
                <a href="/hpe/results" class="btn btn-secondary" style="margin-left: 0.5rem;">Kembali ke Daftar</a>
            </div>`;
            
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="alert alert-error">Error: ' + (data.message || 'Gagal menghitung HPE') + '</div>';
        }
    })
    .catch(err => {
        console.error('Error:', err);
        document.getElementById('result-container').innerHTML = '<div class="alert alert-error">Gagal menghitung HPE.</div>';
    });
});

loadProducts();
</script>
@endpush
@endsection

