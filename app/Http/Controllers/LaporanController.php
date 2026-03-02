<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanController extends Controller
{
    /**
     * Halaman utama laporan dengan preview tabel.
     */
    public function index(Request $request)
    {
        $dari   = $request->input('dari',   now()->startOfMonth()->format('Y-m-d'));
        $sampai = $request->input('sampai', now()->format('Y-m-d'));
        $tab    = $request->input('tab', 'transaksi');

        $data = match($tab) {
            'omzet'        => $this->dataOmzet($dari, $sampai),
            'menu_terlaris'=> $this->dataMenuTerlaris($dari, $sampai),
            default        => $this->dataTransaksi($dari, $sampai),
        };

        // Summary cards (selalu tampil)
        $summary = $this->getSummary($dari, $sampai);

        return view('laporan.index', compact('dari', 'sampai', 'tab', 'data', 'summary'));
    }

    // ============================================================
    // DATA METHODS
    // ============================================================

    private function dataTransaksi(string $dari, string $sampai)
    {
        return Pesanan::with(['details.menu', 'kasir'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$dari, $sampai])
            ->where('status_pembayaran', 'lunas')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($p) {
                $items = $p->details->map(fn($d) =>
                    $d->menu->nama . ' x' . $d->qty
                )->join(', ');
                return [
                    'kode'         => $p->kode_pesanan,
                    'tanggal'      => $p->created_at->format('d/m/Y'),
                    'waktu'        => $p->created_at->format('H:i'),
                    'nama'         => $p->nama_pelanggan,
                    'meja'         => $p->nomor_meja,
                    'items'        => $items,
                    'total'        => $p->total_harga,
                    'total_format' => $p->total_format,
                    'metode'       => strtoupper($p->metode_pembayaran ?? '-'),
                    'kasir'        => $p->kasir?->name ?? '-',
                    'waktu_bayar'  => $p->waktu_bayar ? Carbon::parse($p->waktu_bayar)->format('H:i') : '-',
                ];
            });
    }

    private function dataOmzet(string $dari, string $sampai)
    {
        $rows = Pesanan::selectRaw('
                DATE(created_at) as tanggal,
                COUNT(*) as jumlah_pesanan,
                SUM(total_harga) as total_omzet,
                AVG(total_harga) as rata_rata
            ')
            ->whereBetween(DB::raw('DATE(created_at)'), [$dari, $sampai])
            ->where('status_pembayaran', 'lunas')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('tanggal')
            ->get();

        return $rows->map(function ($r) {
            return [
                'tanggal'          => Carbon::parse($r->tanggal)->format('d/m/Y'),
                'tanggal_raw'      => $r->tanggal,
                'hari'             => Carbon::parse($r->tanggal)->isoFormat('dddd'),
                'jumlah_pesanan'   => $r->jumlah_pesanan,
                'total_omzet'      => $r->total_omzet,
                'total_format'     => 'Rp ' . number_format($r->total_omzet, 0, ',', '.'),
                'rata_rata'        => $r->rata_rata,
                'rata_format'      => 'Rp ' . number_format($r->rata_rata, 0, ',', '.'),
            ];
        });
    }

    private function dataMenuTerlaris(string $dari, string $sampai)
    {
        $rows = DetailPesanan::select(
                'menu_id',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(subtotal) as total_pendapatan')
            )
            ->whereHas('pesanan', function ($q) use ($dari, $sampai) {
                $q->whereBetween(DB::raw('DATE(created_at)'), [$dari, $sampai])
                  ->where('status_pembayaran', 'lunas');
            })
            ->with('menu.kategori')
            ->groupBy('menu_id')
            ->orderByDesc('total_qty')
            ->get();

        return $rows->map(function ($r, $i) {
            return [
                'rank'              => $i + 1,
                'nama'              => $r->menu?->nama ?? '—',
                'kategori'          => $r->menu?->kategori?->nama ?? '—',
                'total_qty'         => $r->total_qty,
                'total_pendapatan'  => $r->total_pendapatan,
                'total_format'      => 'Rp ' . number_format($r->total_pendapatan, 0, ',', '.'),
            ];
        });
    }

    private function getSummary(string $dari, string $sampai): array
    {
        $base = Pesanan::whereBetween(DB::raw('DATE(created_at)'), [$dari, $sampai])
                       ->where('status_pembayaran', 'lunas');

        return [
            'total_transaksi'  => (clone $base)->count(),
            'total_omzet'      => (clone $base)->sum('total_harga'),
            'rata_per_hari'    => $this->rataPerHari($dari, $sampai),
            'menu_terlaris'    => $this->menuTerlarisNama($dari, $sampai),
        ];
    }

    private function rataPerHari(string $dari, string $sampai): float
    {
        $days = Carbon::parse($dari)->diffInDays(Carbon::parse($sampai)) + 1;
        $total = Pesanan::whereBetween(DB::raw('DATE(created_at)'), [$dari, $sampai])
                        ->where('status_pembayaran', 'lunas')
                        ->sum('total_harga');
        return $days > 0 ? $total / $days : 0;
    }

    private function menuTerlarisNama(string $dari, string $sampai): string
    {
        $top = DetailPesanan::select('menu_id', DB::raw('SUM(qty) as total_qty'))
            ->whereHas('pesanan', function ($q) use ($dari, $sampai) {
                $q->whereBetween(DB::raw('DATE(created_at)'), [$dari, $sampai])
                  ->where('status_pembayaran', 'lunas');
            })
            ->with('menu')
            ->groupBy('menu_id')
            ->orderByDesc('total_qty')
            ->first();

        return $top?->menu?->nama ?? '—';
    }

    // ============================================================
    // EXPORT EXCEL
    // ============================================================

    public function exportExcel(Request $request)
    {
        $dari   = $request->input('dari',   now()->startOfMonth()->format('Y-m-d'));
        $sampai = $request->input('sampai', now()->format('Y-m-d'));
        $tab    = $request->input('tab', 'transaksi');

        $filename = 'laporan_' . $tab . '_' . $dari . '_sd_' . $sampai . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LaporanExport($tab, $dari, $sampai),
            $filename
        );
    }

    // ============================================================
    // EXPORT PDF
    // ============================================================

    public function exportPdf(Request $request)
    {
        $dari   = $request->input('dari',   now()->startOfMonth()->format('Y-m-d'));
        $sampai = $request->input('sampai', now()->format('Y-m-d'));
        $tab    = $request->input('tab', 'transaksi');

        $data    = match($tab) {
            'omzet'         => $this->dataOmzet($dari, $sampai),
            'menu_terlaris' => $this->dataMenuTerlaris($dari, $sampai),
            default         => $this->dataTransaksi($dari, $sampai),
        };
        $summary  = $this->getSummary($dari, $sampai);
        $filename = 'laporan_' . $tab . '_' . $dari . '_sd_' . $sampai . '.pdf';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pdf', compact(
            'tab', 'dari', 'sampai', 'data', 'summary'
        ))->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }
}