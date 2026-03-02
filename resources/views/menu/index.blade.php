@extends('layouts.app')

@section('title', 'Kelola Menu')

@section('page-header')
<div class="flex items-center justify-between flex-wrap gap-3">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Kelola Menu</h2>
        <p class="text-sm text-gray-500 mt-0.5">Daftar semua menu Kopi Titik</p>
    </div>
    <div class="flex items-center gap-3">
        {{-- Ringkasan --}}
        <div class="hidden sm:flex items-center gap-3">
            <div class="text-right">
                <p class="text-xs text-gray-400">Total Menu</p>
                <p class="text-lg font-bold text-gray-800" id="count-total">
                    {{ $kategoris->flatMap->menus->count() }}
                </p>
            </div>
            <div class="w-px h-8 bg-gray-200"></div>
            <div class="text-right">
                <p class="text-xs text-gray-400">Tidak Aktif</p>
                <p class="text-lg font-bold text-red-500" id="count-nonaktif">
                    {{ $kategoris->flatMap->menus->where('is_aktif', false)->count() }}
                </p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('create_menu'))
        <a href="{{ route('menu.create') }}"
           class="flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600
                  text-white text-sm font-semibold rounded-xl transition-colors shadow-sm shadow-amber-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Menu
        </a>
        @endif
    </div>
</div>
@endsection

@section('content')

{{-- ============================================================
     SEARCH + FILTER
     ============================================================ --}}
<div class="flex flex-col sm:flex-row gap-3 mb-5">
    {{-- Search --}}
    <div class="relative flex-1">
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" id="search-menu" placeholder="Cari nama menu..."
               class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl
                      focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-100">
    </div>

    {{-- Filter status --}}
    <div class="flex items-center gap-2">
        <button onclick="setFilter('semua')" id="fbtn-semua"
                class="filter-btn aktif px-3 py-2 text-xs font-semibold rounded-xl border-2
                       border-amber-500 bg-amber-500 text-white transition-all">
            Semua
        </button>
        <button onclick="setFilter('aktif')" id="fbtn-aktif"
                class="filter-btn px-3 py-2 text-xs font-semibold rounded-xl border-2
                       border-gray-200 text-gray-600 hover:border-amber-300 transition-all">
            Aktif
        </button>
        <button onclick="setFilter('nonaktif')" id="fbtn-nonaktif"
                class="filter-btn px-3 py-2 text-xs font-semibold rounded-xl border-2
                       border-gray-200 text-gray-600 hover:border-amber-300 transition-all">
            Nonaktif
        </button>
        <button onclick="setFilter('habis')" id="fbtn-habis"
                class="filter-btn px-3 py-2 text-xs font-semibold rounded-xl border-2
                       border-gray-200 text-gray-600 hover:border-red-300 transition-all">
            Stok Habis
        </button>
    </div>
</div>

{{-- ============================================================
     GRID PER KATEGORI
     ============================================================ --}}
<div class="space-y-7" id="wrapper-kategori">

    @forelse($kategoris as $kategori)
    @if($kategori->menus->count() > 0)

    <div class="kategori-section" data-kategori="{{ $kategori->id }}">

        {{-- Header kategori --}}
        <div class="flex items-center gap-2 mb-3">
            <span class="w-[3px] h-5 rounded-full bg-amber-500 flex-shrink-0"></span>
            <h3 class="text-[13px] font-extrabold text-gray-800 uppercase tracking-wider">
                {{ $kategori->nama }}
            </h3>
            <div class="flex-1 h-px bg-gray-100"></div>
            <span class="text-[11px] text-gray-400">{{ $kategori->menus->count() }} item</span>
        </div>

        {{-- Grid card --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">

            @foreach($kategori->menus as $menu)
            <div class="menu-card bg-white rounded-2xl border border-gray-100 overflow-hidden
                        hover:shadow-md hover:border-amber-100 transition-all group"
                 data-id="{{ $menu->id }}"
                 data-nama="{{ strtolower($menu->nama) }}"
                 data-aktif="{{ $menu->is_aktif ? '1' : '0' }}"
                 data-stok="{{ $menu->stok }}">

                {{-- Gambar --}}
                <div class="relative overflow-hidden" style="aspect-ratio:4/3">
                    @if($menu->gambar)
                        <img src="{{ asset('storage/' . $menu->gambar) }}"
                             alt="{{ $menu->nama }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300
                                    {{ !$menu->is_aktif ? 'grayscale opacity-60' : '' }}"
                             loading="lazy">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-3xl
                                    {{ !$menu->is_aktif ? 'opacity-50' : '' }}"
                             style="background:linear-gradient(135deg,#fef3c7,#fde68a)">
                            ☕
                        </div>
                    @endif

                    {{-- Badge status --}}
                    <div class="absolute top-2 left-2 flex flex-col gap-1">
                        @if(!$menu->is_aktif)
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full
                                         bg-gray-800/80 text-white backdrop-blur-sm">
                                Nonaktif
                            </span>
                        @endif
                        @if($menu->stok === 0)
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full
                                         bg-red-500 text-white">
                                Habis
                            </span>
                        @elseif($menu->stok <= 5)
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full
                                         bg-amber-500 text-white">
                                Sisa {{ $menu->stok }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Info --}}
                <div class="p-3">
                    <p class="text-[13px] font-bold text-gray-900 leading-snug line-clamp-2 mb-0.5">
                        {{ $menu->nama }}
                    </p>
                    @if($menu->deskripsi)
                        <p class="text-[11px] text-gray-400 leading-tight line-clamp-1 mb-1.5">
                            {{ $menu->deskripsi }}
                        </p>
                    @endif
                    <p class="text-amber-700 font-extrabold text-[13px] mb-3">
                        {{ $menu->harga_format }}
                    </p>

                    {{-- Tombol aksi --}}
                    @if(auth()->user()->hasPermission('edit_menu') || auth()->user()->hasPermission('delete_menu'))
                    <div class="flex gap-1.5">
                        @if(auth()->user()->hasPermission('edit_menu'))
                        <a href="{{ route('menu.edit', $menu->id) }}"
                           class="flex-1 flex items-center justify-center gap-1 py-1.5 text-[11px] font-semibold
                                  text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200
                                  rounded-lg transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </a>
                        @endif
                        @if(auth()->user()->hasPermission('delete_menu'))
                        <button onclick="hapusMenu({{ $menu->id }}, '{{ addslashes($menu->nama) }}')"
                                class="px-2.5 py-1.5 text-[11px] font-semibold text-red-600 bg-red-50
                                       hover:bg-red-100 border border-red-200 rounded-lg transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                        @endif
                    </div>
                    @endif
                </div>

            </div>
            @endforeach

        </div>
    </div>

    @endif
    @empty
    <div class="bg-white rounded-2xl border border-gray-100 p-16 text-center">
        <div class="text-5xl mb-4">☕</div>
        <p class="text-gray-400 font-medium">Belum ada menu tersedia.</p>
        @if(auth()->user()->hasPermission('create_menu'))
        <a href="{{ route('menu.create') }}"
           class="text-amber-600 text-sm hover:underline mt-2 inline-block font-semibold">
            Tambah menu sekarang →
        </a>
        @endif
    </div>
    @endforelse

</div>

{{-- Kosong setelah filter --}}
<div id="no-results" class="hidden bg-white rounded-2xl border border-gray-100 p-16 text-center mt-4">
    <div class="text-5xl mb-4">🔍</div>
    <p class="text-gray-500 font-semibold">Tidak ada menu yang cocok</p>
    <p class="text-gray-400 text-sm mt-1">Coba ubah kata kunci atau filter</p>
</div>

{{-- ============================================================
     MODAL HAPUS
     ============================================================ --}}
<div id="modal-hapus"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4"
     onclick="if(event.target===this) tutupModal()">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">
        <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </div>
        <h3 class="font-bold text-gray-900 mb-1">Hapus Menu?</h3>
        <p class="text-sm text-gray-500 mb-6">
            Menu <span id="nama-hapus" class="font-semibold text-gray-800"></span>
            akan dihapus permanen.
        </p>
        <div class="flex gap-3">
            <button onclick="tutupModal()"
                    class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100
                           hover:bg-gray-200 rounded-xl transition-colors">
                Batal
            </button>
            <button id="btn-konfirmasi-hapus"
                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-red-600
                           hover:bg-red-700 rounded-xl transition-colors">
                Ya, Hapus
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF      = document.querySelector('meta[name="csrf-token"]').content;
let hapusId     = null;
let filterAktif = 'semua';
let searchQuery = '';

// ============================================
// SEARCH
// ============================================
document.getElementById('search-menu').addEventListener('input', function () {
    searchQuery = this.value.toLowerCase().trim();
    applyFilter();
});

// ============================================
// FILTER
// ============================================
function setFilter(tipe) {
    filterAktif = tipe;
    document.querySelectorAll('.filter-btn').forEach(b => {
        b.classList.remove('bg-amber-500', 'text-white', 'border-amber-500');
        b.classList.add('border-gray-200', 'text-gray-600');
    });
    const aktif = document.getElementById('fbtn-' + tipe);
    aktif.classList.add('bg-amber-500', 'text-white', 'border-amber-500');
    aktif.classList.remove('border-gray-200', 'text-gray-600');
    applyFilter();
}

function applyFilter() {
    const cards = document.querySelectorAll('.menu-card');
    let ada = 0;

    cards.forEach(card => {
        const nama  = card.dataset.nama;
        const aktif = card.dataset.aktif === '1';
        const stok  = parseInt(card.dataset.stok);

        // Filter status
        let lolosFilter = true;
        if (filterAktif === 'aktif')    lolosFilter = aktif;
        if (filterAktif === 'nonaktif') lolosFilter = !aktif;
        if (filterAktif === 'habis')    lolosFilter = stok === 0;

        // Filter search
        const lolosSearch = !searchQuery || nama.includes(searchQuery);

        const tampil = lolosFilter && lolosSearch;
        card.style.display = tampil ? '' : 'none';
        if (tampil) ada++;
    });

    // Sembunyikan kategori yang semua card-nya hidden
    document.querySelectorAll('.kategori-section').forEach(sec => {
        const adaYangTampil = [...sec.querySelectorAll('.menu-card')]
            .some(c => c.style.display !== 'none');
        sec.style.display = adaYangTampil ? '' : 'none';
    });

    document.getElementById('no-results').classList.toggle('hidden', ada > 0);
}

// ============================================
// HAPUS
// ============================================
function hapusMenu(id, nama) {
    hapusId = id;
    document.getElementById('nama-hapus').textContent = nama;
    document.getElementById('modal-hapus').classList.remove('hidden');
    document.getElementById('modal-hapus').classList.add('flex');
}

function tutupModal() {
    hapusId = null;
    document.getElementById('modal-hapus').classList.add('hidden');
    document.getElementById('modal-hapus').classList.remove('flex');
}

document.getElementById('btn-konfirmasi-hapus').addEventListener('click', function () {
    if (!hapusId) return;
    const btn = this;
    btn.textContent = 'Menghapus...';
    btn.disabled    = true;

    fetch(`/menu/${hapusId}`, {
        method:  'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        tutupModal();
        if (data.success) {
            // Hapus card dari DOM
            const card = document.querySelector(`.menu-card[data-id="${hapusId}"]`);
            if (card) card.remove();
            // Sembunyikan kategori kalau kosong
            applyFilter();
            toast('success', data.message);
        } else {
            toast('error', data.message);
        }
    })
    .catch(() => toast('error', 'Gagal menghapus menu.'))
    .finally(() => { btn.textContent = 'Ya, Hapus'; btn.disabled = false; });
});

// ============================================
// TOAST
// ============================================
function toast(tipe, pesan) {
    const warna = tipe === 'success'
        ? 'bg-green-50 border-green-200 text-green-800'
        : 'bg-red-50 border-red-200 text-red-800';
    const el = document.createElement('div');
    el.className = `fixed bottom-6 right-6 z-[99999] px-4 py-3 rounded-xl border
                    shadow-lg text-sm font-medium max-w-sm ${warna}`;
    el.textContent = pesan;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 4000);
}
</script>
@endpush