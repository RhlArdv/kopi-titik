<?php

namespace App\Exports;

use App\Models\Pesanan;
use App\Models\DetailPesanan;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class LaporanExport implements WithMultipleSheets
{
    public function __construct(
        private string $tab,
        private string $dari,
        private string $sampai
    ) {}

    public function sheets(): array
    {
        return match($this->tab) {
            'omzet'         => [new OmzetSheet($this->dari, $this->sampai)],
            'menu_terlaris' => [new MenuTerlarisSheet($this->dari, $this->sampai)],
            default         => [new TransaksiSheet($this->dari, $this->sampai)],
        };
    }
}

// ============================================================
// SHEET 1: TRANSAKSI HARIAN
// ============================================================
class TransaksiSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    public function __construct(private string $dari, private string $sampai) {}

    public function title(): string { return 'Transaksi Harian'; }

    public function collection()
    {
        $rows = Pesanan::with(['details.menu', 'kasir'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$this->dari, $this->sampai])
            ->where('status_pembayaran', 'lunas')
            ->orderByDesc('created_at')
            ->get();

        $no = 1;
        return $rows->map(function ($p) use (&$no) {
            $items = $p->details->map(fn($d) => ($d->menu->nama ?? '?') . ' x' . $d->qty)->join(', ');
            return [
                $no++,
                $p->kode_pesanan,
                $p->created_at->format('d/m/Y'),
                $p->created_at->format('H:i'),
                $p->nama_pelanggan,
                'Meja ' . $p->nomor_meja,
                $items,
                $p->total_harga,
                strtoupper($p->metode_pembayaran ?? '-'),
                $p->kasir?->name ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return ['No', 'Kode Pesanan', 'Tanggal', 'Jam', 'Nama Pelanggan',
                'Meja', 'Item Pesanan', 'Total (Rp)', 'Metode Bayar', 'Kasir'];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,  'B' => 18, 'C' => 12, 'D' => 8,
            'E' => 20, 'F' => 10, 'G' => 45, 'H' => 15,
            'I' => 14, 'J' => 18,
        ];
    }

    public function styles(Worksheet $sheet) { return $this->baseStyle($sheet); }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $this->addHeader($sheet, 'LAPORAN TRANSAKSI HARIAN', $this->dari, $this->sampai, 'J');

                // Format kolom total sebagai angka
                $sheet->getStyle('H3:H' . ($sheet->getHighestRow()))
                      ->getNumberFormat()->setFormatCode('#,##0');
            },
        ];
    }

    private function baseStyle(Worksheet $sheet): array
    {
        return [
            2 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B45309']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    private function addHeader(Worksheet $sheet, string $judul, string $dari, string $sampai, string $lastCol)
    {
        $sheet->insertNewRowBefore(1, 2);
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->mergeCells("A2:{$lastCol}2");

        $sheet->setCellValue('A1', $judul);
        $sheet->setCellValue('A2', 'Periode: ' . Carbon::parse($dari)->format('d M Y') . ' s/d ' . Carbon::parse($sampai)->format('d M Y'));

        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '78350F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['size' => 10, 'color' => ['rgb' => '6B7280']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension(2)->setRowHeight(18);
        $sheet->getRowDimension(3)->setRowHeight(22);
    }
}

// ============================================================
// SHEET 2: OMZET PER PERIODE
// ============================================================
class OmzetSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    public function __construct(private string $dari, private string $sampai) {}

    public function title(): string { return 'Omzet Per Periode'; }

    public function collection()
    {
        $rows = Pesanan::selectRaw('
                DATE(created_at) as tanggal,
                COUNT(*) as jumlah_pesanan,
                SUM(total_harga) as total_omzet,
                AVG(total_harga) as rata_rata
            ')
            ->whereBetween(DB::raw('DATE(created_at)'), [$this->dari, $this->sampai])
            ->where('status_pembayaran', 'lunas')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('tanggal')
            ->get();

        $no = 1;
        $result = $rows->map(function ($r) use (&$no) {
            return [
                $no++,
                Carbon::parse($r->tanggal)->isoFormat('dddd'),
                Carbon::parse($r->tanggal)->format('d/m/Y'),
                $r->jumlah_pesanan,
                (float) $r->total_omzet,
                (float) $r->rata_rata,
            ];
        });

        // Baris total
        $result->push([
            '', 'TOTAL', '',
            $rows->sum('jumlah_pesanan'),
            (float) $rows->sum('total_omzet'),
            '',
        ]);

        return $result;
    }

    public function headings(): array
    {
        return ['No', 'Hari', 'Tanggal', 'Jumlah Pesanan', 'Total Omzet (Rp)', 'Rata-rata / Transaksi (Rp)'];
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 14, 'C' => 14, 'D' => 16, 'E' => 22, 'F' => 26];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            2 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B45309']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $last  = $sheet->getHighestRow();

                // Header
                $sheet->insertNewRowBefore(1, 2);
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');
                $sheet->setCellValue('A1', 'LAPORAN OMZET PER PERIODE');
                $sheet->setCellValue('A2', 'Periode: ' . Carbon::parse($this->dari)->format('d M Y') . ' s/d ' . Carbon::parse($this->sampai)->format('d M Y'));
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '78350F']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '6B7280']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Format angka
                $sheet->getStyle("E4:F{$last}")->getNumberFormat()->setFormatCode('#,##0');

                // Style baris total
                $sheet->getStyle("A{$last}:F{$last}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '78350F']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                ]);
            },
        ];
    }
}

// ============================================================
// SHEET 3: MENU TERLARIS
// ============================================================
class MenuTerlarisSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    public function __construct(private string $dari, private string $sampai) {}

    public function title(): string { return 'Menu Terlaris'; }

    public function collection()
    {
        $rows = DetailPesanan::select(
                'menu_id',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(subtotal) as total_pendapatan')
            )
            ->whereHas('pesanan', function ($q) {
                $q->whereBetween(DB::raw('DATE(created_at)'), [$this->dari, $this->sampai])
                  ->where('status_pembayaran', 'lunas');
            })
            ->with('menu.kategori')
            ->groupBy('menu_id')
            ->orderByDesc('total_qty')
            ->get();

        return $rows->map(function ($r, $i) {
            return [
                $i + 1,
                $r->menu?->nama ?? '—',
                $r->menu?->kategori?->nama ?? '—',
                (int) $r->total_qty,
                (float) $r->total_pendapatan,
            ];
        });
    }

    public function headings(): array
    {
        return ['Rank', 'Nama Menu', 'Kategori', 'Qty Terjual', 'Total Pendapatan (Rp)'];
    }

    public function columnWidths(): array
    {
        return ['A' => 7, 'B' => 30, 'C' => 20, 'D' => 14, 'E' => 24];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            2 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B45309']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $last  = $sheet->getHighestRow();

                $sheet->insertNewRowBefore(1, 2);
                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:E2');
                $sheet->setCellValue('A1', 'LAPORAN MENU TERLARIS');
                $sheet->setCellValue('A2', 'Periode: ' . Carbon::parse($this->dari)->format('d M Y') . ' s/d ' . Carbon::parse($this->sampai)->format('d M Y'));
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '78350F']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '6B7280']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle("E4:E{$last}")->getNumberFormat()->setFormatCode('#,##0');

                // Top 3 highlight
                foreach ([4, 5, 6] as $row) {
                    $colors = ['FFD700', 'C0C0C0', 'CD7F32'];
                    $idx = $row - 4;
                    if (isset($colors[$idx])) {
                        $sheet->getStyle("A{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => '1C1917']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colors[$idx]]],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }
                }
            },
        ];
    }
}