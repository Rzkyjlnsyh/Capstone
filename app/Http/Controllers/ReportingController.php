<?php

namespace App\Http\Controllers;

use App\Models\HpeResult;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportingController extends Controller
{
public function exportHpe(Request $request): Response|JsonResponse|BinaryFileResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:pdf,excel'],
            'hpe_result_id' => ['nullable', 'exists:hpe_results,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $query = HpeResult::with('product', 'exchangeRate', 'calculatedBy');

        if ($data['hpe_result_id'] ?? null) {
            $query->where('id', $data['hpe_result_id']);
        }

        if ($data['product_id'] ?? null) {
            $query->where('product_id', $data['product_id']);
        }

        if ($data['date_from'] ?? null) {
            $query->where('calculated_at', '>=', $data['date_from']);
        }

        if ($data['date_to'] ?? null) {
            $query->where('calculated_at', '<=', $data['date_to']);
        }

        $results = $query->orderBy('calculated_at', 'desc')->get();

        if ($results->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data HPE untuk diekspor',
            ], 404);
        }

        if ($data['type'] === 'pdf') {
            return $this->exportPdf($results);
        }

        return $this->exportExcel($results);
    }

    private function exportPdf($results)
    {
        $pdf = Pdf::loadView('reports.hpe-pdf', [
            'results' => $results,
            'generated_at' => now()->format('d/m/Y H:i:s'),
        ]);

        return $pdf->download('hpe-report-' . now()->format('Y-m-d') . '.pdf');
    }

    private function exportExcel($results): BinaryFileResponse
    {
        $data = $results->map(function ($result) {
            $breakdown = $result->component_breakdown ?? [];
            $components = collect($breakdown)->map(fn ($item) => ($item['component_name'] ?? '') . ' (' . ($item['quantity'] ?? 0) . ' ' . ($item['unit'] ?? '') . ')')->join(', ');

            return [
                'ID' => $result->id,
                'Tanggal' => $result->calculated_at->format('d/m/Y H:i'),
                'Produk' => $result->product->name ?? '-',
                'Kode Produk' => $result->product->code ?? '-',
                'Margin (%)' => $result->margin_percent,
                'Total Biaya (IDR)' => $result->total_cost_idr,
                'Total dengan Margin (IDR)' => $result->total_with_margin,
                'Komponen' => $components,
                'Kurs USD/IDR' => $result->exchangeRate->rate_value ?? '-',
                'Dihitung Oleh' => $result->calculatedBy->name ?? '-',
                'Status' => $result->status,
            ];
        })->toArray();

        return Excel::download(
            new class($data) implements \Maatwebsite\Excel\Concerns\FromArray {
                public function __construct(private array $data) {}

                public function array(): array
                {
                    return $this->data;
                }
            },
            'hpe-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportProducts(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:pdf,excel'],
        ]);

        $products = Product::with('productComponents.component')->get();

        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data produk untuk diekspor',
            ], 404);
        }

        if ($data['type'] === 'pdf') {
            $pdf = Pdf::loadView('reports.products-pdf', [
                'products' => $products,
                'generated_at' => now()->format('d/m/Y H:i:s'),
            ]);

            return $pdf->download('products-report-' . now()->format('Y-m-d') . '.pdf');
        }

        $excelData = $products->map(function ($product) {
            $components = $product->productComponents->map(fn ($pc) => ($pc->component->name ?? '') . ' (' . $pc->quantity . ' ' . ($pc->component->unit ?? '') . ')')->join(', ');

            return [
                'Kode' => $product->code,
                'Nama' => $product->name,
                'Deskripsi' => $product->description ?? '-',
                'Kategori' => $product->category ?? '-',
                'Komponen' => $components,
                'Status' => $product->status,
            ];
        })->toArray();

        return Excel::download(
            new class($excelData) implements \Maatwebsite\Excel\Concerns\FromArray {
                public function __construct(private array $data) {}

                public function array(): array
                {
                    return $this->data;
                }
            },
            'products-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
