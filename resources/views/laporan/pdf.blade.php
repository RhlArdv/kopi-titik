<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; margin: 0; padding: 0; }
    body { padding: 20px; color: #1c1917; }

    .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #b45309; padding-bottom: 12px; }
    .header h1 { font-size: 16px; font-weight: bold; color: #78350f; margin-bottom: 4px; }
    .header p  { font-size: 10px; color: #6b7280; }

    .summary { display: flex; gap: 10px; margin-bottom: 16px; }
    .summary-card {
        flex: 1; background: #fef3c7; border: 1px solid #fde68a;
        border-radius: 6px; padding: 10px 12px;
    }
    .summary-card .label { font-size: 9px; color: #92400e; font-weight: bold; text-transform: uppercase; }
    .summary-card .value { font-size: 13px; font-weight: bold; color: #78350f; margin-top: 2px; }

    table { width: 100%; border-collapse: collapse; margin-top: 4px; }
    thead tr { background: #b45309; }
    thead th { color: white; padding: 7px 8px; text-align: left; font-size: 10px; font-weight: bold; }
    tbody tr:nth-child(even) { background: #fafafa; }
    tbody tr:hover { background: #fef9ee; }
    tbody td { padding: 6px 8px; border-bottom: 1px solid #f3f4f6; font-size: 10px; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .total-row td { background: #fef3c7 !important; font-weight: bold; color: #78350f; }

    .rank-1 { background: #ffd700 !important; font-weight: bold; }
    .rank-2 { background: #e8e8e8 !important; font-weight: bold; }
    .rank-3 { background: #f4c57b !important; font-weight: bold; }

    .footer { margin-top: 20px; text-align: right; font-size: 9px; color: #9ca3af; }
</style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <h1>
        @if($tab === 'transaksi') LAPORAN TRANSAKSI HARIAN
        @elseif($tab === 'omzet') LAPORAN OMZET PER PERIODE
        @else LAPORAN MENU TERLARIS
        @endif
        — KOPI TITIK
    </h1>
    <p>
        Periode: {{ \Carbon\Carbon::parse($dari)->isoFormat('D MMMM Y') }}
        s/d {{ \Carbon\Carbon::parse($sampai)->isoFormat('D MMMM Y') }}
        &nbsp;|&nbsp; Dicetak: {{ now()->isoFormat('D MMMM Y, HH:mm') }}
    </p>
</div>

{{-- Summary cards --}}
<table style="width:100%; margin-bottom:16px; border-collapse:separate; border-spacing:6px;">
    <tr>
        <td style="background:#fef3c7; border:1px solid #fde68a; border-radius:6px; padding:8px 12px; width:25%">
            <div style="font-size:9px; color:#92400e; font-weight:bold; text-transform:uppercase">Total Transaksi</div>
            <div style="font-size:14px; font-weight:bold; color:#78350f; margin-top:2px">{{ number_format($summary['total_transaksi']) }}</div>
        </td>
        <td style="background:#fef3c7; border:1px solid #fde68a; border-radius:6px; padding:8px 12px; width:25%">
            <div style="font-size:9px; color:#92400e; font-weight:bold; text-transform:uppercase">Total Omzet</div>
            <div style="font-size:14px; font-weight:bold; color:#78350f; margin-top:2px">Rp {{ number_format($summary['total_omzet'], 0, ',', '.') }}</div>
        </td>
        <td style="background:#fef3c7; border:1px solid #fde68a; border-radius:6px; padding:8px 12px; width:25%">
            <div style="font-size:9px; color:#92400e; font-weight:bold; text-transform:uppercase">Rata-rata / Hari</div>
            <div style="font-size:14px; font-weight:bold; color:#78350f; margin-top:2px">Rp {{ number_format($summary['rata_per_hari'], 0, ',', '.') }}</div>
        </td>
        <td style="background:#fef3c7; border:1px solid #fde68a; border-radius:6px; padding:8px 12px; width:25%">
            <div style="font-size:9px; color:#92400e; font-weight:bold; text-transform:uppercase">Menu Terlaris</div>
            <div style="font-size:12px; font-weight:bold; color:#78350f; margin-top:2px">{{ $summary['menu_terlaris'] }}</div>
        </td>
    </tr>
</table>

{{-- Tabel data --}}
@if($tab === 'transaksi')
<table>
    <thead>
        <tr>
            <th style="width:30px">No</th>
            <th>Kode</th>
            <th>Tgl</th>
            <th>Jam</th>
            <th>Pelanggan</th>
            <th>Meja</th>
            <th>Item Pesanan</th>
            <th class="text-right">Total</th>
            <th>Metode</th>
            <th>Kasir</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $i => $row)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $row['kode'] }}</td>
            <td>{{ $row['tanggal'] }}</td>
            <td>{{ $row['waktu'] }}</td>
            <td>{{ $row['nama'] }}</td>
            <td class="text-center">{{ $row['meja'] }}</td>
            <td style="max-width:120px; overflow:hidden">{{ $row['items'] }}</td>
            <td class="text-right">{{ $row['total_format'] }}</td>
            <td class="text-center">{{ $row['metode'] }}</td>
            <td>{{ $row['kasir'] }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="7" class="text-right">TOTAL</td>
            <td class="text-right">Rp {{ number_format($data->sum('total'), 0, ',', '.') }}</td>
            <td colspan="2"></td>
        </tr>
    </tbody>
</table>

@elseif($tab === 'omzet')
<table>
    <thead>
        <tr>
            <th style="width:30px">No</th>
            <th>Hari</th>
            <th>Tanggal</th>
            <th class="text-center">Jumlah Pesanan</th>
            <th class="text-right">Total Omzet</th>
            <th class="text-right">Rata-rata / Transaksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $i => $row)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $row['hari'] }}</td>
            <td>{{ $row['tanggal'] }}</td>
            <td class="text-center">{{ $row['jumlah_pesanan'] }}</td>
            <td class="text-right">{{ $row['total_format'] }}</td>
            <td class="text-right">{{ $row['rata_format'] }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="3" class="text-right">TOTAL</td>
            <td class="text-center">{{ $data->sum('jumlah_pesanan') }}</td>
            <td class="text-right">Rp {{ number_format($data->sum('total_omzet'), 0, ',', '.') }}</td>
            <td></td>
        </tr>
    </tbody>
</table>

@else {{-- menu_terlaris --}}
<table>
    <thead>
        <tr>
            <th style="width:40px" class="text-center">Rank</th>
            <th>Nama Menu</th>
            <th>Kategori</th>
            <th class="text-center">Qty Terjual</th>
            <th class="text-right">Total Pendapatan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
        <tr class="{{ $row['rank'] === 1 ? 'rank-1' : ($row['rank'] === 2 ? 'rank-2' : ($row['rank'] === 3 ? 'rank-3' : '')) }}">
            <td class="text-center">
                @if($row['rank'] === 1)
                    <svg class="w-5 h-5 mx-auto" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="9" r="6" fill="#FFD700" stroke="#DAA520" stroke-width="1"/>
                        <path d="M8 14L6 18L8 17L12 18L16 17L18 18L16 14" fill="#FF6347" stroke="#DC143C" stroke-width="0.5"/>
                        <text x="12" y="11" text-anchor="middle" font-size="7" font-weight="bold" fill="#B8860B">1</text>
                    </svg>
                @elseif($row['rank'] === 2)
                    <svg class="w-5 h-5 mx-auto" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="9" r="6" fill="#C0C0C0" stroke="#808080" stroke-width="1"/>
                        <path d="M8 14L6 18L8 17L12 18L16 17L18 18L16 14" fill="#4169E1" stroke="#0000CD" stroke-width="0.5"/>
                        <text x="12" y="11" text-anchor="middle" font-size="7" font-weight="bold" fill="#696969">2</text>
                    </svg>
                @elseif($row['rank'] === 3)
                    <svg class="w-5 h-5 mx-auto" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="9" r="6" fill="#CD7F32" stroke="#8B4513" stroke-width="1"/>
                        <path d="M8 14L6 18L8 17L12 18L16 17L18 18L16 14" fill="#228B22" stroke="#006400" stroke-width="0.5"/>
                        <text x="12" y="11" text-anchor="middle" font-size="7" font-weight="bold" fill="#8B4513">3</text>
                    </svg>
                @else
                    {{ $row['rank'] }}
                @endif
            </td>
            <td>{{ $row['nama'] }}</td>
            <td>{{ $row['kategori'] }}</td>
            <td class="text-center">{{ number_format($row['total_qty']) }}</td>
            <td class="text-right">{{ $row['total_format'] }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="3" class="text-right">TOTAL</td>
            <td class="text-center">{{ number_format($data->sum('total_qty')) }}</td>
            <td class="text-right">Rp {{ number_format($data->sum('total_pendapatan'), 0, ',', '.') }}</td>
        </tr>
    </tbody>
</table>
@endif

<div class="footer">
    Kopi Titik — Sistem Informasi Reservasi Menu QR Code &nbsp;|&nbsp; {{ now()->isoFormat('D MMMM Y, HH:mm') }}
</div>

</body>
</html>