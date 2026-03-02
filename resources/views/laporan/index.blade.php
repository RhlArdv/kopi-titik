@extends('layouts.app')

@section('title', 'Laporan')

@section('page-header')
<div class="flex items-center justify-between flex-wrap gap-3">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Laporan</h1>
        <p class="text-[13px] text-gray-500 mt-0.5">Data transaksi, omzet, dan menu terlaris</p>
    </div>
    {{-- Export buttons --}}
    <div class="flex items-center gap-2">
        {{-- <a href="{{ route('laporan.export-excel', ['dari' => $dari, 'sampai' => $sampai, 'tab' => $tab]) }}"
           class="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700
                  text-white text-sm font-semibold rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            </svg>
            Excel
        </a> --}}
        <a href="{{ route('laporan.export-pdf', ['dari' => $dari, 'sampai' => $sampai, 'tab' => $tab]) }}"
           class="flex items-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700
                  text-white text-sm font-semibold rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            PDF
        </a>
    </div>
</div>
@endsection

@section('content')

{{-- ============================================================
     FILTER TANGGAL
     ============================================================ --}}
<form method="GET" action="{{ route('laporan.index') }}"
      class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5">
    <div class="flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Dari Tanggal</label>
            <input type="date" name="dari" value="{{ $dari }}"
                   class="border border-gray-200 rounded-xl px-3 py-2 text-sm
                          focus:outline-none focus:border-amber-400">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Sampai Tanggal</label>
            <input type="date" name="sampai" value="{{ $sampai }}"
                   class="border border-gray-200 rounded-xl px-3 py-2 text-sm
                          focus:outline-none focus:border-amber-400">
        </div>
        <input type="hidden" name="tab" value="{{ $tab }}">

        {{-- Shortcut periode --}}
        <div class="flex items-center gap-1.5">
            <button type="button" onclick="setPeriode('hari_ini')"
                    class="px-3 py-2 text-xs font-semibold text-gray-600 bg-gray-100
                           hover:bg-gray-200 rounded-xl transition-colors">
                Hari Ini
            </button>
            <button type="button" onclick="setPeriode('minggu_ini')"
                    class="px-3 py-2 text-xs font-semibold text-gray-600 bg-gray-100
                           hover:bg-gray-200 rounded-xl transition-colors">
                Minggu Ini
            </button>
            <button type="button" onclick="setPeriode('bulan_ini')"
                    class="px-3 py-2 text-xs font-semibold text-gray-600 bg-gray-100
                           hover:bg-gray-200 rounded-xl transition-colors">
                Bulan Ini
            </button>
        </div>

        <button type="submit"
                class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white
                       text-sm font-semibold rounded-xl transition-colors ml-auto">
            Terapkan
        </button>
    </div>
</form>

{{-- ============================================================
     SUMMARY CARDS
     ============================================================ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Total Transaksi</p>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_transaksi']) }}</p>
        <p class="text-xs text-gray-400 mt-1">pesanan lunas</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Total Omzet</p>
        <p class="text-2xl font-bold text-amber-700">
            Rp {{ number_format($summary['total_omzet'], 0, ',', '.') }}
        </p>
        <p class="text-xs text-gray-400 mt-1">periode ini</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Rata-rata / Hari</p>
        <p class="text-2xl font-bold text-gray-900">
            Rp {{ number_format($summary['rata_per_hari'], 0, ',', '.') }}
        </p>
        <p class="text-xs text-gray-400 mt-1">omzet harian</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Menu Terlaris</p>
        <p class="text-base font-bold text-gray-900 leading-tight mt-1">{{ $summary['menu_terlaris'] }}</p>
        <p class="text-xs text-gray-400 mt-1">periode ini</p>
    </div>

</div>

{{-- ============================================================
     TAB JENIS LAPORAN
     ============================================================ --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    {{-- Tab header --}}
    <div class="flex border-b border-gray-100">
        @foreach([
            ['key' => 'transaksi',     'label' => 'Transaksi Harian',  'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            ['key' => 'omzet',         'label' => 'Omzet Per Periode', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['key' => 'menu_terlaris', 'label' => 'Menu Terlaris',     'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
        ] as $tabItem)
        <a href="{{ route('laporan.index', ['dari' => $dari, 'sampai' => $sampai, 'tab' => $tabItem['key']]) }}"
           class="flex items-center gap-2 px-5 py-4 text-sm font-semibold transition-colors border-b-2
                  {{ $tab === $tabItem['key']
                     ? 'text-amber-700 border-amber-500 bg-amber-50/50'
                     : 'text-gray-500 border-transparent hover:text-gray-700 hover:bg-gray-50' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $tabItem['icon'] }}"/>
            </svg>
            <span class="hidden sm:inline">{{ $tabItem['label'] }}</span>
        </a>
        @endforeach

        {{-- Info jumlah data --}}
        <div class="ml-auto flex items-center px-5 text-xs text-gray-400">
            {{ count($data) }} data
        </div>
    </div>

    {{-- ============================================================
         TABEL: TRANSAKSI HARIAN
         ============================================================ --}}
    @if($tab === 'transaksi')
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[800px]">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left text-xs font-semibold text-gray-500 px-4 py-3">No</th>
                    <th class="text-left text-xs font-semibold text-gray-500 px-4 py-3">Kode</th>
                    <th class="text-left text-xs font-semibold text-gray-500 px-4 py-3">Tanggal</th>
                    <th class="text-left text-xs font-semibold text-gray-500 px-4 py-3">Jam</th>
                    <th class="text-left text-xs font-semibold text-gray-500 px-4 py-3">Pelanggan</th>
                    <th class="text-left text-xs font-semibold text-gray-500 px-4 py-3">Meja</th>
                    <th class="text-left text-xs font-semibold text-gray-500 px-4 py-3">Item</th>
                    <th class="text-right text-xs font-semibold text-gray-500 px-4 py-3">Total</th>
                    <th class="text-left text-xs font-semibold text-gray-500 px-4 py-3">Metode</th>
                    <th class="text-left text-xs font-semibold text-gray-500 px-4 py-3">Kasir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $i => $row)
                <tr class="border-b border-gray-50 hover:bg-amber-50/30 transition-colors">
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                    <td class="px-4 py-3">
                        <span class="font-mono text-xs bg-gray-100 px-2 py-0.5 rounded-lg">
                            {{ $row['kode'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-[13px] text-gray-700">{{ $row['tanggal'] }}</td>
                    <td class="px-4 py-3 text-[13px] text-gray-500">{{ $row['waktu'] }}</td>
                    <td class="px-4 py-3 text-[13px] font-medium text-gray-800">{{ $row['nama'] }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs bg-amber-100 text-amber-700 font-bold px-2 py-0.5 rounded-lg">
                            {{ $row['meja'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-[12px] text-gray-500 max-w-[200px] truncate" title="{{ $row['items'] }}">
                        {{ $row['items'] }}
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-900 text-[13px]">
                        {{ $row['total_format'] }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full
                            {{ $row['metode'] === 'QRIS'
                               ? 'bg-blue-100 text-blue-700'
                               : 'bg-green-100 text-green-700' }}">
                            {{ $row['metode'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-[13px] text-gray-600">{{ $row['kasir'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-4 py-12 text-center text-gray-400 text-sm">
                        Tidak ada transaksi pada periode ini
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if(count($data) > 0)
            <tfoot>
                <tr class="bg-amber-50 border-t-2 border-amber-200">
                    <td colspan="7" class="px-4 py-3 text-right text-xs font-bold text-amber-800">
                        TOTAL ({{ count($data) }} transaksi)
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-amber-900">
                        Rp {{ number_format($data->sum('total'), 0, ',', '.') }}
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- ============================================================
         TABEL: OMZET PER PERIODE
         ============================================================ --}}
    @elseif($tab === 'omzet')
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left text-xs font-semibold text-gray-500 px-4 py-3">No</th>
                    <th class="text-left text-xs font-semibold text-gray-500 px-4 py-3">Hari</th>
                    <th class="text-left text-xs font-semibold text-gray-500 px-4 py-3">Tanggal</th>
                    <th class="text-center text-xs font-semibold text-gray-500 px-4 py-3">Jumlah Pesanan</th>
                    <th class="text-right text-xs font-semibold text-gray-500 px-4 py-3">Total Omzet</th>
                    <th class="text-right text-xs font-semibold text-gray-500 px-4 py-3">Rata-rata / Transaksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $i => $row)
                <tr class="border-b border-gray-50 hover:bg-amber-50/30 transition-colors">
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                    <td class="px-4 py-3 text-[13px] text-gray-500">{{ $row['hari'] }}</td>
                    <td class="px-4 py-3 text-[13px] font-medium text-gray-800">{{ $row['tanggal'] }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                     bg-amber-100 text-amber-800 text-xs font-bold">
                            {{ $row['jumlah_pesanan'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-gray-900">{{ $row['total_format'] }}</td>
                    <td class="px-4 py-3 text-right text-gray-500 text-[13px]">{{ $row['rata_format'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-gray-400 text-sm">
                        Tidak ada data omzet pada periode ini
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if(count($data) > 0)
            <tfoot>
                <tr class="bg-amber-50 border-t-2 border-amber-200">
                    <td colspan="3" class="px-4 py-3 text-right text-xs font-bold text-amber-800">TOTAL</td>
                    <td class="px-4 py-3 text-center font-bold text-amber-900">
                        {{ $data->sum('jumlah_pesanan') }}
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-amber-900">
                        Rp {{ number_format($data->sum('total_omzet'), 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- ============================================================
         TABEL: MENU TERLARIS
         ============================================================ --}}
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-center text-xs font-semibold text-gray-500 px-4 py-3 w-16">Rank</th>
                    <th class="text-left text-xs font-semibold text-gray-500 px-4 py-3">Nama Menu</th>
                    <th class="text-left text-xs font-semibold text-gray-500 px-4 py-3">Kategori</th>
                    <th class="text-center text-xs font-semibold text-gray-500 px-4 py-3">Qty Terjual</th>
                    <th class="text-right text-xs font-semibold text-gray-500 px-4 py-3">Total Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr class="border-b border-gray-50 hover:bg-amber-50/30 transition-colors
                           {{ $row['rank'] <= 3 ? 'bg-amber-50/20' : '' }}">
                    <td class="px-4 py-3 text-center">
                        @if($row['rank'] === 1)
                            <svg class="w-6 h-6 mx-auto" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="9" r="6" fill="#FFD700" stroke="#DAA520" stroke-width="1"/>
                                <path d="M8 14L6 18L8 17L12 18L16 17L18 18L16 14" fill="#FF6347" stroke="#DC143C" stroke-width="0.5"/>
                                <text x="12" y="11" text-anchor="middle" font-size="7" font-weight="bold" fill="#B8860B">1</text>
                            </svg>
                        @elseif($row['rank'] === 2)
                            <svg class="w-6 h-6 mx-auto" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="9" r="6" fill="#C0C0C0" stroke="#808080" stroke-width="1"/>
                                <path d="M8 14L6 18L8 17L12 18L16 17L18 18L16 14" fill="#4169E1" stroke="#0000CD" stroke-width="0.5"/>
                                <text x="12" y="11" text-anchor="middle" font-size="7" font-weight="bold" fill="#696969">2</text>
                            </svg>
                        @elseif($row['rank'] === 3)
                            <svg class="w-6 h-6 mx-auto" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="9" r="6" fill="#CD7F32" stroke="#8B4513" stroke-width="1"/>
                                <path d="M8 14L6 18L8 17L12 18L16 17L18 18L16 14" fill="#228B22" stroke="#006400" stroke-width="0.5"/>
                                <text x="12" y="11" text-anchor="middle" font-size="7" font-weight="bold" fill="#8B4513">3</text>
                            </svg>
                        @else
                            <span class="text-xs font-bold text-gray-400">#{{ $row['rank'] }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-[13px] font-semibold text-gray-900">{{ $row['nama'] }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-[11px] bg-gray-100 text-gray-600 px-2 py-0.5 rounded-lg font-medium">
                            {{ $row['kategori'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center min-w-[36px] px-2 py-1 rounded-full
                                     bg-amber-100 text-amber-800 text-xs font-bold">
                            {{ number_format($row['total_qty']) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-gray-900">{{ $row['total_format'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-gray-400 text-sm">
                        Tidak ada data menu terjual pada periode ini
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if(count($data) > 0)
            <tfoot>
                <tr class="bg-amber-50 border-t-2 border-amber-200">
                    <td colspan="3" class="px-4 py-3 text-right text-xs font-bold text-amber-800">TOTAL</td>
                    <td class="px-4 py-3 text-center font-bold text-amber-900">
                        {{ number_format($data->sum('total_qty')) }}
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-amber-900">
                        Rp {{ number_format($data->sum('total_pendapatan'), 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    @endif

</div>

@endsection

@push('scripts')
<script>
function setPeriode(tipe) {
    const today = new Date();
    let dari, sampai;

    if (tipe === 'hari_ini') {
        dari = sampai = today.toISOString().split('T')[0];
    } else if (tipe === 'minggu_ini') {
        const day  = today.getDay() || 7;
        const mon  = new Date(today); mon.setDate(today.getDate() - day + 1);
        const sun  = new Date(mon);   sun.setDate(mon.getDate() + 6);
        dari   = mon.toISOString().split('T')[0];
        sampai = sun.toISOString().split('T')[0];
    } else {
        dari   = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
        sampai = today.toISOString().split('T')[0];
    }

    document.querySelector('input[name="dari"]').value   = dari;
    document.querySelector('input[name="sampai"]').value = sampai;
}
</script>
@endpush