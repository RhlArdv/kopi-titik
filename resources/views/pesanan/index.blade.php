@extends('layouts.app')

@section('title', 'Pesanan Masuk')

@section('page-header')
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Pesanan Masuk</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ request('tanggal') ? \Carbon\Carbon::parse(request('tanggal'))->isoFormat('dddd, D MMMM YYYY') : 'Hari ini, ' . now()->isoFormat('D MMMM YYYY') }}
            </p>
        </div>
        {{-- Counter badge status --}}
        <div class="flex items-center gap-3 flex-wrap">
            <div class="flex items-center gap-1.5 text-xs font-medium">
                <span class="w-2 h-2 rounded-full bg-yellow-400"></span>
                <span class="text-gray-500">Menunggu:</span>
                <span class="font-bold text-gray-800">{{ $ringkasan['menunggu'] }}</span>
            </div>
            <div class="flex items-center gap-1.5 text-xs font-medium">
                <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                <span class="text-gray-500">Diproses:</span>
                <span class="font-bold text-gray-800">{{ $ringkasan['diproses'] }}</span>
            </div>
            <div class="flex items-center gap-1.5 text-xs font-medium">
                <span class="w-2 h-2 rounded-full bg-red-400"></span>
                <span class="text-gray-500">Belum Bayar:</span>
                <span class="font-bold text-red-600">{{ $ringkasan['belum_bayar'] }}</span>
            </div>
        </div>
    </div>
@endsection

@section('content')

    {{-- ============================================
         FILTER BAR
         ============================================ --}}
    <form method="GET" action="{{ route('pesanan.index') }}"
          class="bg-white rounded-xl border border-gray-200 p-4 mb-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

            {{-- Status Pesanan --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status Pesanan</label>
                <select name="status_pesanan"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:border-amber-400">
                    <option value="">Semua Status</option>
                    <option value="menunggu"  {{ request('status_pesanan') === 'menunggu'  ? 'selected' : '' }}>Menunggu</option>
                    <option value="diproses"  {{ request('status_pesanan') === 'diproses'  ? 'selected' : '' }}>Diproses</option>
                    <option value="selesai"   {{ request('status_pesanan') === 'selesai'   ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>

            {{-- Status Pembayaran --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status Pembayaran</label>
                <select name="status_pembayaran"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:border-amber-400">
                    <option value="">Semua</option>
                    <option value="belum_bayar" {{ request('status_pembayaran') === 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
                    <option value="lunas"       {{ request('status_pembayaran') === 'lunas'       ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>

            {{-- Tanggal --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal</label>
                <input type="date"
                       name="tanggal"
                       value="{{ request('tanggal', today()->format('Y-m-d')) }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:border-amber-400">
            </div>

            {{-- Nomor Meja --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nomor Meja</label>
                <div class="flex gap-2">
                    <input type="text"
                           name="nomor_meja"
                           value="{{ request('nomor_meja') }}"
                           placeholder="Cari meja..."
                           class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm
                                  focus:outline-none focus:border-amber-400">
                    <button type="submit"
                            class="px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white
                                   rounded-lg transition-colors text-sm font-medium">
                        Cari
                    </button>
                </div>
            </div>

        </div>

        {{-- Reset filter --}}
        @if(request()->hasAny(['status_pesanan', 'status_pembayaran', 'nomor_meja']) ||
            request('tanggal') !== today()->format('Y-m-d'))
            <div class="mt-3 pt-3 border-t border-gray-100">
                <a href="{{ route('pesanan.index') }}"
                   class="text-xs text-amber-600 hover:text-amber-700 font-medium">
                    ↩ Reset ke hari ini
                </a>
            </div>
        @endif
    </form>

    {{-- ============================================
         GRID CARD PESANAN
         ============================================ --}}
    @if($pesanans->count() > 0)

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-5">
            @foreach($pesanans as $pesanan)

                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden
                            hover:shadow-md transition-shadow duration-200"
                     id="card-pesanan-{{ $pesanan->id }}">

                    {{-- Header card --}}
                    <div class="px-4 py-3 flex items-center justify-between border-b border-gray-100
                                {{ $pesanan->status_pesanan === 'menunggu' ? 'bg-yellow-50' :
                                   ($pesanan->status_pesanan === 'diproses' ? 'bg-blue-50' : 'bg-green-50') }}">
                        <div>
                            <p class="text-xs font-bold text-gray-500 tracking-wider">{{ $pesanan->kode_pesanan }}</p>
                            <p class="text-sm font-semibold text-gray-800 mt-0.5">
                                Meja {{ $pesanan->nomor_meja }}
                                <span class="font-normal text-gray-500">· {{ $pesanan->nama_pelanggan }}</span>
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            {{-- Badge status pesanan --}}
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                         {{ $pesanan->status_pesanan_badge }}"
                                  id="badge-status-{{ $pesanan->id }}">
                                {{ $pesanan->status_pesanan_label }}
                            </span>
                            {{-- Badge status bayar --}}
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                         {{ $pesanan->status_bayar_badge }}">
                                {{ $pesanan->status_pembayaran === 'lunas' ? 'Lunas' : 'Belum Bayar' }}
                            </span>
                        </div>
                    </div>

                    {{-- Isi pesanan --}}
                    <div class="px-4 py-3">
                        <ul class="space-y-1 mb-3">
                            @foreach($pesanan->details->take(3) as $detail)
                                <li class="flex items-center justify-between text-xs text-gray-600">
                                    <span class="truncate mr-2">
                                        {{ $detail->qty }}× {{ $detail->nama_menu_saat_pesan }}
                                        @if($detail->catatan_item)
                                            <span class="text-gray-400 italic">({{ $detail->catatan_item }})</span>
                                        @endif
                                    </span>
                                    <span class="flex-shrink-0 font-medium">{{ $detail->subtotal_format }}</span>
                                </li>
                            @endforeach
                            @if($pesanan->details->count() > 3)
                                <li class="text-xs text-gray-400 italic">
                                    +{{ $pesanan->details->count() - 3 }} item lainnya...
                                </li>
                            @endif
                        </ul>

                        {{-- Total + waktu --}}
                        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                            <span class="text-xs text-gray-400">
                                {{ $pesanan->created_at->format('H:i') }}
                            </span>
                            <span class="text-sm font-bold text-gray-900">{{ $pesanan->total_format }}</span>
                        </div>
                    </div>

                    {{-- Tombol aksi --}}
                    <div class="px-4 pb-4 flex items-center gap-2 flex-wrap">

                        {{-- Detail --}}
                        <button onclick="lihatDetail({{ $pesanan->id }})"
                                class="flex-1 px-3 py-2 text-xs font-medium text-gray-600 bg-gray-100
                                       hover:bg-gray-200 rounded-lg transition-colors">
                            Detail
                        </button>

                        {{-- Update Status (head bar & admin) --}}
                        @if(auth()->user()->hasPermission('proses_pesanan'))
                            @if($pesanan->status_pesanan === 'menunggu')
                                <button onclick="updateStatus({{ $pesanan->id }}, 'diproses')"
                                        class="flex-1 px-3 py-2 text-xs font-medium text-white bg-blue-500
                                               hover:bg-blue-600 rounded-lg transition-colors">
                                    Proses
                                </button>
                            @elseif($pesanan->status_pesanan === 'diproses')
                                <button onclick="updateStatus({{ $pesanan->id }}, 'selesai')"
                                        class="flex-1 px-3 py-2 text-xs font-medium text-white bg-green-500
                                               hover:bg-green-600 rounded-lg transition-colors">
                                    Selesai
                                </button>
                            @endif
                        @endif

                        {{-- Konfirmasi Bayar (kasir & admin) --}}
                        @if(auth()->user()->hasPermission('konfirmasi_pembayaran') && $pesanan->status_pembayaran === 'belum_bayar')
                            <button onclick="bukaModalBayar({{ $pesanan->id }}, '{{ $pesanan->kode_pesanan }}', {{ $pesanan->total_harga }})"
                                    class="flex-1 px-3 py-2 text-xs font-medium text-white bg-amber-500
                                           hover:bg-amber-600 rounded-lg transition-colors">
                                Bayar
                            </button>
                        @endif

                        {{-- Hapus (admin only) --}}
                        @if(auth()->user()->hasPermission('delete_pesanan'))
                            <button onclick="hapusPesanan({{ $pesanan->id }}, '{{ $pesanan->kode_pesanan }}')"
                                    class="px-3 py-2 text-xs font-medium text-red-600 bg-red-50
                                           hover:bg-red-100 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        @endif

                    </div>
                </div>

            @endforeach
        </div>

        {{-- Pagination --}}
        {{ $pesanans->links() }}

    @else
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <p class="text-gray-500 font-medium">Tidak ada pesanan ditemukan</p>
            <p class="text-sm text-gray-400 mt-1">Coba ubah filter atau pilih tanggal lain</p>
        </div>
    @endif

    {{-- ============================================
         MODAL DETAIL PESANAN
         ============================================ --}}
    <div id="modal-detail"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4"
         onclick="if(event.target===this) tutupModal('modal-detail')">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">

            <div class="sticky top-0 bg-white px-5 py-4 border-b border-gray-100 flex items-center justify-between rounded-t-2xl">
                <div>
                    <h3 class="font-semibold text-gray-900" id="detail-kode"></h3>
                    <p class="text-xs text-gray-400" id="detail-waktu"></p>
                </div>
                <button onclick="tutupModal('modal-detail')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-5 py-4 space-y-4">

                {{-- Info pelanggan --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-400 mb-0.5">Pelanggan</p>
                        <p class="text-sm font-semibold text-gray-800" id="detail-pelanggan"></p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-400 mb-0.5">Nomor Meja</p>
                        <p class="text-sm font-semibold text-gray-800" id="detail-meja"></p>
                    </div>
                </div>

                {{-- Catatan --}}
                <div id="wrapper-catatan" class="hidden">
                    <p class="text-xs text-gray-400 mb-1">Catatan</p>
                    <p class="text-sm text-gray-700 bg-amber-50 rounded-lg px-3 py-2 border border-amber-100"
                       id="detail-catatan"></p>
                </div>

                {{-- Daftar item --}}
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Item Pesanan</p>
                    <div id="detail-items" class="space-y-2"></div>
                </div>

                {{-- Total --}}
                <div class="border-t border-gray-100 pt-3 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700">Total</span>
                    <span class="text-base font-bold text-gray-900" id="detail-total"></span>
                </div>

                {{-- Info pembayaran (jika sudah lunas) --}}
                <div id="wrapper-info-bayar" class="hidden bg-green-50 rounded-lg p-3 space-y-1">
                    <p class="text-xs font-semibold text-green-700 mb-2">✓ Sudah Lunas</p>
                    <div class="flex justify-between text-xs text-green-700">
                        <span>Metode</span>
                        <span class="font-medium capitalize" id="detail-metode"></span>
                    </div>
                    <div id="wrapper-detail-cash" class="hidden">
                        <div class="flex justify-between text-xs text-green-700">
                            <span>Bayar</span>
                            <span class="font-medium" id="detail-nominal"></span>
                        </div>
                        <div class="flex justify-between text-xs text-green-700">
                            <span>Kembalian</span>
                            <span class="font-medium" id="detail-kembalian"></span>
                        </div>
                    </div>
                    <div class="flex justify-between text-xs text-green-700">
                        <span>Kasir</span>
                        <span class="font-medium" id="detail-kasir"></span>
                    </div>
                    <div class="flex justify-between text-xs text-green-700">
                        <span>Waktu Bayar</span>
                        <span class="font-medium" id="detail-waktu-bayar"></span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ============================================
         MODAL KONFIRMASI PEMBAYARAN
         ============================================ --}}
    <div id="modal-bayar"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4"
         onclick="if(event.target===this) tutupModal('modal-bayar')">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm">

            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-900">Konfirmasi Pembayaran</h3>
                    <p class="text-xs text-gray-400 mt-0.5" id="bayar-kode"></p>
                </div>
                <button onclick="tutupModal('modal-bayar')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-5 py-4 space-y-4">

                {{-- Total tagihan --}}
                <div class="bg-amber-50 rounded-xl p-4 text-center border border-amber-100">
                    <p class="text-xs text-amber-600 mb-1">Total Tagihan</p>
                    <p class="text-2xl font-bold text-amber-900" id="bayar-total"></p>
                </div>

                {{-- Pilih metode --}}
                <div>
                    <p class="text-xs font-medium text-gray-500 mb-2">Metode Pembayaran</p>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="metode-btn cursor-pointer">
                            <input type="radio" name="metode_pembayaran" value="cash"
                                   class="sr-only" onchange="pilihMetode('cash')">
                            <div class="border-2 border-gray-200 rounded-xl p-3 text-center transition-all
                                        peer-checked:border-amber-500 hover:border-amber-300"
                                 id="btn-cash">
                                <svg class="w-6 h-6 mx-auto mb-1" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="2" y="5" width="20" height="14" rx="2" fill="#4CAF50" stroke="#2E7D32" stroke-width="1.5"/>
                                    <circle cx="7" cy="12" r="2" fill="#FFF"/>
                                    <rect x="11" y="8" width="8" height="2" rx="1" fill="#FFF"/>
                                    <rect x="11" y="11" width="6" height="2" rx="1" fill="#FFF"/>
                                    <rect x="11" y="14" width="4" height="2" rx="1" fill="#FFF"/>
                                </svg>
                                <p class="text-xs font-semibold text-gray-700">Cash</p>
                            </div>
                        </label>
                        <label class="metode-btn cursor-pointer">
                            <input type="radio" name="metode_pembayaran" value="qris"
                                   class="sr-only" onchange="pilihMetode('qris')">
                            <div class="border-2 border-gray-200 rounded-xl p-3 text-center transition-all
                                        hover:border-amber-300"
                                 id="btn-qris">
                                <p class="text-lg mb-1">📱</p>
                                <p class="text-xs font-semibold text-gray-700">QRIS</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Input nominal (hanya untuk cash) --}}
                <div id="wrapper-nominal" class="hidden">
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">
                        Nominal Dibayar (Rp)
                    </label>
                    <input type="number"
                           id="input-nominal"
                           min="0"
                           step="1000"
                           placeholder="Masukkan nominal uang..."
                           class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                  focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400"
                           oninput="hitungKembalian()">

                    {{-- Preview kembalian --}}
                    <div id="preview-kembalian"
                         class="hidden mt-2 bg-green-50 rounded-lg px-3 py-2 border border-green-100">
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Kembalian:</span>
                            <span class="font-bold text-green-700" id="nilai-kembalian">Rp 0</span>
                        </div>
                    </div>

                    {{-- Warning kurang bayar --}}
                    <div id="warning-kurang"
                         class="hidden mt-2 bg-red-50 rounded-lg px-3 py-2 border border-red-100">
                        <p class="text-xs text-red-600">⚠ Nominal kurang dari total tagihan</p>
                    </div>
                </div>

                {{-- Info QRIS --}}
                <div id="info-qris" class="hidden bg-blue-50 rounded-lg px-3 py-2 border border-blue-100">
                    <p class="text-xs text-blue-700">
                        📱 Pastikan pelanggan sudah transfer via QRIS sebelum konfirmasi.
                    </p>
                </div>

                <p id="error-bayar" class="text-xs text-red-500 hidden"></p>

            </div>

            <div class="px-5 pb-5 flex gap-3">
                <button onclick="tutupModal('modal-bayar')"
                        class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100
                               hover:bg-gray-200 rounded-lg transition-colors">
                    Batal
                </button>
                <button id="btn-konfirmasi-bayar"
                        onclick="konfirmasiBayar()"
                        class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-amber-500
                               hover:bg-amber-600 rounded-lg transition-colors">
                    Konfirmasi
                </button>
            </div>

        </div>
    </div>

    {{-- ============================================
         MODAL HAPUS PESANAN (admin only)
         ============================================ --}}
    <div id="modal-hapus"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40"
         onclick="if(event.target===this) tutupModal('modal-hapus')">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-100 mx-auto mb-4">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <h3 class="text-center font-semibold text-gray-900 mb-1">Hapus Pesanan?</h3>
            <p class="text-center text-sm text-gray-500 mb-6">
                Pesanan <span id="kode-hapus" class="font-semibold text-gray-800"></span>
                akan dihapus permanen.
            </p>
            <div class="flex gap-3">
                <button onclick="tutupModal('modal-hapus')"
                        class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100
                               hover:bg-gray-200 rounded-lg transition-colors">
                    Batal
                </button>
                <button id="btn-konfirmasi-hapus"
                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600
                               hover:bg-red-700 rounded-lg transition-colors">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    const csrfToken    = document.querySelector('meta[name="csrf-token"]').content;
    let pesananIdBayar = null;
    let totalTagihan   = 0;
    let metodeDipilih  = null;
    let pesananIdHapus = null;

    // ============================================
    // LIHAT DETAIL
    // ============================================
    function lihatDetail(id) {
        fetch(`/pesanan/${id}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        })
        .then(res => res.json())
        .then(({ data }) => {
            document.getElementById('detail-kode').textContent      = data.kode_pesanan;
            document.getElementById('detail-waktu').textContent     = data.created_at;
            document.getElementById('detail-pelanggan').textContent = data.nama_pelanggan;
            document.getElementById('detail-meja').textContent      = 'Meja ' + data.nomor_meja;
            document.getElementById('detail-total').textContent     = data.total_format;

            // Catatan
            if (data.catatan) {
                document.getElementById('detail-catatan').textContent = data.catatan;
                document.getElementById('wrapper-catatan').classList.remove('hidden');
            } else {
                document.getElementById('wrapper-catatan').classList.add('hidden');
            }

            // Items
            const itemsHtml = data.details.map(d => `
                <div class="flex items-start justify-between text-sm">
                    <div class="flex-1 mr-2">
                        <span class="font-medium text-gray-800">${d.qty}× ${d.nama}</span>
                        <span class="text-gray-400 text-xs"> @ ${d.harga}</span>
                        ${d.catatan_item ? `<p class="text-xs text-amber-600 italic mt-0.5">${d.catatan_item}</p>` : ''}
                    </div>
                    <span class="font-semibold text-gray-800 flex-shrink-0">${d.subtotal}</span>
                </div>
            `).join('');
            document.getElementById('detail-items').innerHTML = itemsHtml;

            // Info pembayaran
            if (data.status_pembayaran === 'lunas') {
                document.getElementById('wrapper-info-bayar').classList.remove('hidden');
                document.getElementById('detail-metode').textContent    = data.metode_pembayaran;
                document.getElementById('detail-kasir').textContent     = data.kasir_nama || '-';
                document.getElementById('detail-waktu-bayar').textContent = data.waktu_bayar || '-';

                if (data.metode_pembayaran === 'cash') {
                    document.getElementById('wrapper-detail-cash').classList.remove('hidden');
                    document.getElementById('detail-nominal').textContent   = 'Rp ' + Number(data.nominal_bayar).toLocaleString('id-ID');
                    document.getElementById('detail-kembalian').textContent = 'Rp ' + Number(data.kembalian).toLocaleString('id-ID');
                } else {
                    document.getElementById('wrapper-detail-cash').classList.add('hidden');
                }
            } else {
                document.getElementById('wrapper-info-bayar').classList.add('hidden');
            }

            bukaModal('modal-detail');
        })
        .catch(() => tampilToast('error', 'Gagal memuat detail pesanan.'));
    }

    // ============================================
    // UPDATE STATUS PESANAN
    // ============================================
    function updateStatus(id, statusBaru) {
        fetch(`/pesanan/${id}/status`, {
            method:  'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept':       'application/json',
            },
            body: JSON.stringify({ status: statusBaru }),
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                tampilToast('success', data.message);
                // Reload halaman setelah sebentar agar card refresh
                setTimeout(() => location.reload(), 800);
            } else {
                tampilToast('error', data.message);
            }
        })
        .catch(() => tampilToast('error', 'Gagal update status.'));
    }

    // ============================================
    // MODAL BAYAR
    // ============================================
    function bukaModalBayar(id, kode, total) {
        pesananIdBayar = id;
        totalTagihan   = total;
        metodeDipilih  = null;

        document.getElementById('bayar-kode').textContent  = kode;
        document.getElementById('bayar-total').textContent = 'Rp ' + Number(total).toLocaleString('id-ID');
        document.getElementById('input-nominal').value     = '';
        document.getElementById('error-bayar').classList.add('hidden');
        document.getElementById('wrapper-nominal').classList.add('hidden');
        document.getElementById('info-qris').classList.add('hidden');
        document.getElementById('preview-kembalian').classList.add('hidden');
        document.getElementById('warning-kurang').classList.add('hidden');

        // Reset pilihan metode
        document.querySelectorAll('input[name="metode_pembayaran"]').forEach(r => r.checked = false);
        document.getElementById('btn-cash').classList.remove('border-amber-500', 'bg-amber-50');
        document.getElementById('btn-qris').classList.remove('border-amber-500', 'bg-amber-50');

        bukaModal('modal-bayar');
    }

    function pilihMetode(metode) {
        metodeDipilih = metode;

        // Reset styling semua
        document.getElementById('btn-cash').classList.remove('border-amber-500', 'bg-amber-50');
        document.getElementById('btn-qris').classList.remove('border-amber-500', 'bg-amber-50');

        // Aktifkan yang dipilih
        document.getElementById('btn-' + metode).classList.add('border-amber-500', 'bg-amber-50');

        if (metode === 'cash') {
            document.getElementById('wrapper-nominal').classList.remove('hidden');
            document.getElementById('info-qris').classList.add('hidden');
            setTimeout(() => document.getElementById('input-nominal').focus(), 100);
        } else {
            document.getElementById('wrapper-nominal').classList.add('hidden');
            document.getElementById('info-qris').classList.remove('hidden');
            document.getElementById('preview-kembalian').classList.add('hidden');
            document.getElementById('warning-kurang').classList.add('hidden');
        }
    }

    function hitungKembalian() {
        const nominal    = parseFloat(document.getElementById('input-nominal').value) || 0;
        const kembalian  = nominal - totalTagihan;
        const previewEl  = document.getElementById('preview-kembalian');
        const warningEl  = document.getElementById('warning-kurang');
        const nilaiEl    = document.getElementById('nilai-kembalian');

        if (nominal <= 0) {
            previewEl.classList.add('hidden');
            warningEl.classList.add('hidden');
            return;
        }

        if (kembalian < 0) {
            previewEl.classList.add('hidden');
            warningEl.classList.remove('hidden');
        } else {
            warningEl.classList.add('hidden');
            previewEl.classList.remove('hidden');
            nilaiEl.textContent = 'Rp ' + kembalian.toLocaleString('id-ID');
        }
    }

    function konfirmasiBayar() {
        const errorEl = document.getElementById('error-bayar');
        const btn     = document.getElementById('btn-konfirmasi-bayar');
        errorEl.classList.add('hidden');

        if (!metodeDipilih) {
            errorEl.textContent = 'Pilih metode pembayaran terlebih dahulu.';
            errorEl.classList.remove('hidden');
            return;
        }

        const payload = { metode_pembayaran: metodeDipilih };

        if (metodeDipilih === 'cash') {
            const nominal = parseFloat(document.getElementById('input-nominal').value);
            if (!nominal || nominal < totalTagihan) {
                errorEl.textContent = 'Nominal bayar tidak mencukupi.';
                errorEl.classList.remove('hidden');
                return;
            }
            payload.nominal_bayar = nominal;
        }

        btn.textContent = 'Memproses...';
        btn.disabled    = true;

        fetch(`/pesanan/${pesananIdBayar}/bayar`, {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept':       'application/json',
            },
            body: JSON.stringify(payload),
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                tutupModal('modal-bayar');

                // Tampilkan kembalian dulu sebelum reload
                if (data.kembalian_format) {
                    tampilToast('success', `✓ Lunas! Kembalian: ${data.kembalian_format}`);
                } else {
                    tampilToast('success', data.message);
                }

                setTimeout(() => location.reload(), 1500);
            } else {
                errorEl.textContent = data.message;
                errorEl.classList.remove('hidden');
            }
        })
        .catch(() => {
            errorEl.textContent = 'Terjadi kesalahan. Coba lagi.';
            errorEl.classList.remove('hidden');
        })
        .finally(() => {
            btn.textContent = 'Konfirmasi';
            btn.disabled    = false;
        });
    }

    // ============================================
    // HAPUS PESANAN (admin)
    // ============================================
    function hapusPesanan(id, kode) {
        pesananIdHapus = id;
        document.getElementById('kode-hapus').textContent = kode;
        bukaModal('modal-hapus');
    }

    document.getElementById('btn-konfirmasi-hapus')?.addEventListener('click', function () {
        if (!pesananIdHapus) return;
        const btn = this;
        btn.textContent = 'Menghapus...';
        btn.disabled    = true;

        fetch(`/pesanan/${pesananIdHapus}`, {
            method:  'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        })
        .then(res => res.json())
        .then(data => {
            tutupModal('modal-hapus');
            if (data.success) {
                document.getElementById('card-pesanan-' + pesananIdHapus)?.remove();
                tampilToast('success', data.message);
            } else {
                tampilToast('error', data.message);
            }
        })
        .catch(() => tampilToast('error', 'Gagal menghapus pesanan.'))
        .finally(() => { btn.textContent = 'Ya, Hapus'; btn.disabled = false; });
    });

    // ============================================
    // HELPER
    // ============================================
    function bukaModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.getElementById(id).classList.add('flex');
    }

    function tutupModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    }

    function tampilToast(tipe, pesan) {
        const warna = tipe === 'success'
            ? 'bg-green-50 border-green-200 text-green-800'
            : 'bg-red-50 border-red-200 text-red-800';
        const toast = document.createElement('div');
        toast.className = `fixed bottom-6 right-6 z-50 flex items-center gap-2 px-4 py-3
                           rounded-xl border shadow-lg text-sm font-medium max-w-sm ${warna}`;
        toast.textContent = pesan;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }
</script>
@endpush