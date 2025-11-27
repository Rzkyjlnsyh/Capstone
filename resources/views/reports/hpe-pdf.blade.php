<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan HPE (Harga Perkiraan Estimasi)</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; }
        h1 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .header-info { margin-bottom: 20px; }
        .footer { margin-top: 30px; font-size: 9pt; color: #666; text-align: right; }
    </style>
</head>
<body>
    <h1>Laporan HPE (Harga Perkiraan Estimasi)</h1>
    
    <div class="header-info">
        <p><strong>Tanggal Generate:</strong> {{ $generated_at }}</p>
        <p><strong>Jumlah Data:</strong> {{ $results->count() }} hasil perhitungan</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Produk</th>
                <th>Total Biaya (IDR)</th>
                <th>Margin (%)</th>
                <th>Total dengan Margin (IDR)</th>
                <th>Kurs USD/IDR</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $index => $result)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $result->calculated_at->format('d/m/Y H:i') }}</td>
                <td>{{ $result->product->name ?? '-' }}<br><small>({{ $result->product->code ?? '-' }})</small></td>
                <td>{{ number_format($result->total_cost_idr, 0, ',', '.') }}</td>
                <td>{{ $result->margin_percent }}%</td>
                <td><strong>{{ number_format($result->total_with_margin, 0, ',', '.') }}</strong></td>
                <td>{{ $result->exchangeRate->rate_value ?? '-' }}</td>
                <td>{{ $result->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ $generated_at }}</p>
    </div>
</body>
</html>

