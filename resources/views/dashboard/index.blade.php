@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-[13px] text-gray-500 mt-0.5">
            {{ now()->isoFormat('dddd, D MMMM YYYY') }}
        </p>
    </div>
    <div class="hidden sm:flex items-center gap-2 bg-white border border-gray-200
                rounded-xl px-3.5 py-2 text-[13px] text-gray-600 shadow-sm">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        Hari ini, {{ now()->format('d M Y') }}
    </div>
</div>
@endsection

@section('content')

@php
    use App\Models\Pesanan;
    use App\Models\Menu;

    $pesananHariIni   = Pesanan::whereDate('created_at', today())->count();
    $menungguBayar    = Pesanan::whereDate('created_at', today())->where('status_pembayaran', 'belum_bayar')->count();
    $omzetHariIni     = Pesanan::whereDate('created_at', today())->where('status_pembayaran', 'lunas')->sum('total_harga');
    $stokHabis        = Menu::where('stok', 0)->where('is_aktif', true)->count();

    // Data chart 7 hari terakhir
    $chartData = collect(range(6, 0))->map(function ($daysAgo) {
        $date = now()->subDays($daysAgo);
        return [
            'label'   => $date->isoFormat('ddd'),
            'tanggal' => $date->format('Y-m-d'),
            'omzet'   => Pesanan::whereDate('created_at', $date)->where('status_pembayaran', 'lunas')->sum('total_harga'),
            'count'   => Pesanan::whereDate('created_at', $date)->count(),
        ];
    });

    // Pesanan terbaru
    $pesananTerbaru = Pesanan::with('details')->whereDate('created_at', today())
        ->orderByDesc('created_at')->take(5)->get();

    // Menu hampir habis
    $menuHampirHabis = Menu::with('kategori')->where('stok', '<=', 5)
        ->where('is_aktif', true)->orderBy('stok')->take(5)->get();
@endphp

{{-- ================================================
     STAT CARDS
     ================================================ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

    {{-- Pesanan Hari Ini --}}
    @if(auth()->user()->hasPermission('view_pesanan'))
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-4">
            <div class="w-10 h-10 rounded-xl bg-sky-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <span class="text-[11px] font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">
                Hari ini
            </span>
        </div>
        <p class="text-2xl font-bold text-gray-900 leading-none">{{ number_format($pesananHariIni) }}</p>
        <p class="text-[13px] text-gray-500 mt-1">Total Pesanan</p>
    </div>
    @endif

    {{-- Belum Bayar --}}
    @if(auth()->user()->hasPermission('konfirmasi_pembayaran') || auth()->user()->hasPermission('view_pesanan'))
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-4">
            <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            @if($menungguBayar > 0)
                <span class="text-[11px] font-semibold text-red-600 bg-red-50 px-2 py-0.5 rounded-full">
                    Perlu aksi
                </span>
            @else
                <span class="text-[11px] font-semibold text-gray-400 bg-gray-50 px-2 py-0.5 rounded-full">
                    Clear
                </span>
            @endif
        </div>
        <p class="text-2xl font-bold {{ $menungguBayar > 0 ? 'text-red-600' : 'text-gray-900' }} leading-none">
            {{ number_format($menungguBayar) }}
        </p>
        <p class="text-[13px] text-gray-500 mt-1">Belum Bayar</p>
    </div>
    @endif

    {{-- Omzet Hari Ini --}}
    @if(auth()->user()->hasPermission('view_laporan') || auth()->user()->isAdmin())
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-4">
            <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="text-[11px] font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">
                Lunas
            </span>
        </div>
        <p class="text-2xl font-bold text-gray-900 leading-none">
            Rp {{ number_format($omzetHariIni, 0, ',', '.') }}
        </p>
        <p class="text-[13px] text-gray-500 mt-1">Omzet Hari Ini</p>
    </div>
    @endif

    {{-- Stok Habis --}}
    @if(auth()->user()->hasPermission('view_stok'))
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-4">
            <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            @if($stokHabis > 0)
                <span class="text-[11px] font-semibold text-red-600 bg-red-50 px-2 py-0.5 rounded-full">
                    Perlu isi ulang
                </span>
            @else
                <span class="text-[11px] font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">
                    Aman
                </span>
            @endif
        </div>
        <p class="text-2xl font-bold {{ $stokHabis > 0 ? 'text-red-600' : 'text-gray-900' }} leading-none">
            {{ $stokHabis }}
        </p>
        <p class="text-[13px] text-gray-500 mt-1">Menu Stok Habis</p>
    </div>
    @endif

</div>

{{-- ================================================
     CHART + TABEL (2 kolom)
     ================================================ --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">

    {{-- Chart omzet 7 hari --}}
    @if(auth()->user()->hasPermission('view_laporan') || auth()->user()->isAdmin())
    <div class="xl:col-span-2 bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-[15px] font-bold text-gray-900">Omzet 7 Hari Terakhir</h3>
                <p class="text-xs text-gray-400 mt-0.5">Transaksi yang sudah lunas</p>
            </div>
        </div>

        {{-- Bar chart --}}
        <div class="flex items-end justify-between gap-2 h-36">
            @php $maxOmzet = $chartData->max('omzet') ?: 1; @endphp
            @foreach($chartData as $day)
                @php
                    $height  = $maxOmzet > 0 ? round(($day['omzet'] / $maxOmzet) * 100) : 0;
                    $isToday = $day['tanggal'] === now()->format('Y-m-d');
                @endphp
                <div class="flex-1 flex flex-col items-center gap-1.5">
                    <div class="w-full flex items-end justify-center" style="height: 112px">
                        <div class="w-full rounded-t-lg transition-all duration-500 relative group cursor-pointer
                                    {{ $isToday ? 'bg-sky-500' : 'bg-sky-100 hover:bg-sky-200' }}"
                             style="height: {{ max($height, 4) }}%">
                            {{-- Tooltip --}}
                            <div class="hidden group-hover:flex absolute -top-8 left-1/2 -translate-x-1/2
                                        bg-gray-900 text-white text-[10px] rounded-lg px-2 py-1
                                        whitespace-nowrap z-10">
                                Rp {{ number_format($day['omzet'], 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                    <p class="text-[11px] {{ $isToday ? 'font-bold text-sky-600' : 'text-gray-400' }}">
                        {{ $day['label'] }}
                    </p>
                </div>
            @endforeach
        </div>

        {{-- Summary bawah chart --}}
        <div class="flex items-center gap-4 mt-4 pt-4 border-t border-gray-100">
            <div>
                <p class="text-xs text-gray-400">Total 7 hari</p>
                <p class="text-sm font-bold text-gray-900">
                    Rp {{ number_format($chartData->sum('omzet'), 0, ',', '.') }}
                </p>
            </div>
            <div class="w-px h-8 bg-gray-100"></div>
            <div>
                <p class="text-xs text-gray-400">Total pesanan</p>
                <p class="text-sm font-bold text-gray-900">{{ $chartData->sum('count') }} pesanan</p>
            </div>
            <div class="w-px h-8 bg-gray-100"></div>
            <div>
                <p class="text-xs text-gray-400">Rata-rata/hari</p>
                <p class="text-sm font-bold text-gray-900">
                    Rp {{ number_format($chartData->avg('omzet'), 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- Menu hampir habis --}}
    @if(auth()->user()->hasPermission('view_stok'))
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-[15px] font-bold text-gray-900">Stok Kritis</h3>
            <a href="{{ route('stok.index') }}"
               class="text-xs text-sky-600 hover:text-sky-700 font-semibold">
                Kelola →
            </a>
        </div>

        @if($menuHampirHabis->count() > 0)
            <div class="space-y-3">
                @foreach($menuHampirHabis as $menu)
                    <div class="flex items-center gap-3">
                        <img src="{{ $menu->gambar_url }}"
                             alt="{{ $menu->nama }}"
                             class="w-9 h-9 rounded-xl object-cover border border-gray-100 flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-medium text-gray-800 truncate">{{ $menu->nama }}</p>
                            <p class="text-[11px] text-gray-400">{{ $menu->kategori->nama }}</p>
                        </div>
                        <span class="flex-shrink-0 text-[11px] font-bold px-2 py-1 rounded-lg
                                     {{ $menu->stok == 0
                                         ? 'bg-red-100 text-red-700'
                                         : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $menu->stok == 0 ? 'Habis' : $menu->stok . ' sisa' }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="h-32 flex flex-col items-center justify-center text-center">
                <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-[13px] font-medium text-gray-700">Semua stok aman</p>
                <p class="text-xs text-gray-400 mt-0.5">Tidak ada menu yang hampir habis</p>
            </div>
        @endif
    </div>
    @endif

</div>

{{-- ================================================
     PESANAN TERBARU
     ================================================ --}}
@if(auth()->user()->hasPermission('view_pesanan'))
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-[15px] font-bold text-gray-900">Pesanan Terbaru Hari Ini</h3>
        <a href="{{ route('pesanan.index') }}"
           class="text-xs text-sky-600 hover:text-sky-700 font-semibold">
            Lihat semua →
        </a>
    </div>

    @if($pesananTerbaru->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-50">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Kode</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Pelanggan</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Meja</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Item</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($pesananTerbaru as $p)
                        <tr class="hover:bg-sky-50/40 transition-colors">
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-xs font-semibold text-gray-600">{{ $p->kode_pesanan }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-[13px] font-medium text-gray-800">{{ $p->nama_pelanggan }}</td>
                            <td class="px-5 py-3.5 text-[13px] text-gray-500">{{ $p->nomor_meja }}</td>
                            <td class="px-5 py-3.5 text-[13px] text-gray-500">{{ $p->details->count() }} item</td>
                            <td class="px-5 py-3.5 text-[13px] font-bold text-gray-900">{{ $p->total_format }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold
                                             {{ $p->status_pesanan_badge }}">
                                    {{ $p->status_pesanan_label }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-[12px] text-gray-400">{{ $p->created_at->format('H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="py-12 text-center">
            <p class="text-[13px] text-gray-400">Belum ada pesanan hari ini</p>
        </div>
    @endif
</div>
@endif

@endsection