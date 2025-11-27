<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Produk & Komponen</title>
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
    <h1>Laporan Produk & Komponen</h1>
    
    <div class="header-info">
        <p><strong>Tanggal Generate:</strong> {{ $generated_at }}</p>
        <p><strong>Jumlah Produk:</strong> {{ $products->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Komponen</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $index => $product)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $product->code }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category ?? '-' }}</td>
                <td>
                    @foreach($product->productComponents as $pc)
                        {{ $pc->component->name ?? '-' }} ({{ $pc->quantity }} {{ $pc->component->unit ?? '' }})<br>
                    @endforeach
                </td>
                <td>{{ $product->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ $generated_at }}</p>
    </div>
</body>
</html>

