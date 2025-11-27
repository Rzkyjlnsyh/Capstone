@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
<h2>Audit Log</h2>

<div id="audit-logs-container" style="margin-top: 1.5rem;">
    <p>Memuat data...</p>
</div>

@push('scripts')
<script>
let apiToken = localStorage.getItem('api_token') || '';

function loadAuditLogs() {
    fetch('/api/audit-logs?per_page=50', {
        headers: {
            'Authorization': 'Bearer ' + apiToken,
            'Accept': 'application/json'
        }
    })
    .then(res => {
        if (res.status === 403) {
            document.getElementById('audit-logs-container').innerHTML = '<div class="alert alert-error">Akses ditolak. Hanya admin yang dapat melihat audit log.</div>';
            return;
        }
        return res.json();
    })
    .then(data => {
        if (!data) return;
        const container = document.getElementById('audit-logs-container');
        if (data.data && data.data.length > 0) {
            let html = '<table><thead><tr><th>Tanggal</th><th>User</th><th>Entity</th><th>Aksi</th><th>Detail</th></tr></thead><tbody>';
            data.data.forEach(log => {
                html += `<tr>
                    <td>${log.created_at ? new Date(log.created_at).toLocaleString('id-ID') : '-'}</td>
                    <td>${log.user ? log.user.name : '-'}</td>
                    <td>${log.entity_type || '-'} #${log.entity_id || '-'}</td>
                    <td>${log.action || '-'}</td>
                    <td><small>${log.changes ? JSON.stringify(log.changes).substring(0, 50) + '...' : '-'}</small></td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p>Tidak ada audit log ditemukan.</p>';
        }
    })
    .catch(err => {
        console.error('Error:', err);
        document.getElementById('audit-logs-container').innerHTML = '<div class="alert alert-error">Gagal memuat data.</div>';
    });
}

loadAuditLogs();
</script>
@endpush
@endsection

