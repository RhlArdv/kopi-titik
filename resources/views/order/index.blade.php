@extends('layouts.guest')

@section('title', 'Pesan Menu')
@section('header-subtitle', 'Pesan langsung dari meja kamu')

@section('content')

    {{-- ============================================
         FORM IDENTITAS PELANGGAN
         ============================================ --}}
    <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-5 mb-5">
        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-amber-500 text-white text-xs flex items-center justify-center font-bold">1</span>
            Isi Data Kamu
        </h3>
        <div class="grid grid-cols-2 gap-3">
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Nama <span class="text-red-400">*</span></label>
                <input type="text"
                       id="input-nama"
                       placeholder="Nama kamu..."
                       maxlength="100"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm
                              focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-200">
                <p id="error-nama" class="text-xs text-red-500 mt-1 hidden"></p>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Nomor Meja <span class="text-red-400">*</span></label>
                <input type="text"
                       id="input-meja"
                       placeholder="Contoh: 5"
                       maxlength="20"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm
                              focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-200">
                <p id="error-meja" class="text-xs text-red-500 mt-1 hidden"></p>
            </div>
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-500 mb-1.5">
                    Catatan <span class="text-gray-300 font-normal">(opsional)</span>
                </label>
                <input type="text"
                       id="input-catatan"
                       placeholder="Contoh: tidak pakai es, less sugar..."
                       maxlength="300"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm
                              focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-200">
            </div>
        </div>
    </div>

    {{-- ============================================
         MENU PER KATEGORI
         ============================================ --}}
    <div class="mb-24"> {{-- padding bawah untuk floating cart --}}

        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-amber-500 text-white text-xs flex items-center justify-center font-bold">2</span>
            Pilih Menu
        </h3>

        {{-- Tab kategori --}}
        <div class="flex gap-2 overflow-x-auto pb-2 mb-4 scrollbar-hide">
            <button onclick="filterKategori('all')"
                    id="tab-all"
                    class="tab-kategori flex-shrink-0 px-4 py-1.5 rounded-full text-xs font-semibold
                           border transition-all bg-amber-500 text-white border-amber-500">
                Semua
            </button>
            @foreach($kategoris as $kategori)
                <button onclick="filterKategori('{{ $kategori->id }}')"
                        id="tab-{{ $kategori->id }}"
                        class="tab-kategori flex-shrink-0 px-4 py-1.5 rounded-full text-xs font-semibold
                               border transition-all bg-white text-gray-600 border-gray-200 hover:border-amber-400"
                        data-kategori="{{ $kategori->id }}">
                    {{ $kategori->nama }}
                </button>
            @endforeach
        </div>

        {{-- Grid semua menu --}}
        <div id="menu-container" class="grid grid-cols-2 gap-3">
            @php
                // Flatten semua menu dari semua kategori
                $allMenus = collect([]);
                foreach($kategoris as $kategori) {
                    foreach($kategori->menusAktif as $menu) {
                        $menu->kategori_nama = $kategori->nama;
                        $menu->kategori_id = $kategori->id;
                        $allMenus->push($menu);
                    }
                }
                // Sort: stok > 0 dulu, baru stok <= 0
                $allMenus = $allMenus->sortByDesc(function($menu) {
                    return $menu->stok > 0;
                })->values();
            @endphp

            @foreach($allMenus as $menu)
                @php $habis = $menu->stok <= 0; @endphp

                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden
                            {{ $habis ? 'opacity-60' : 'hover:shadow-md hover:border-amber-200' }}
                            transition-all duration-200 relative menu-card"
                     id="card-menu-{{ $menu->id }}"
                     data-kategori-id="{{ $menu->kategori_id }}">

                    {{-- Gambar --}}
                    <div class="relative aspect-square overflow-hidden bg-amber-50">
                        <img src="{{ $menu->gambar_url }}"
                             alt="{{ $menu->nama }}"
                             class="w-full h-full object-cover {{ $habis ? 'grayscale' : '' }}">

                        {{-- Overlay habis --}}
                        @if($habis)
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                <span class="bg-white text-gray-700 text-xs font-bold px-3 py-1 rounded-full shadow">
                                    Habis
                                </span>
                            </div>
                        @endif

                        {{-- Badge qty di cart --}}
                        <div id="badge-qty-{{ $menu->id }}"
                             class="hidden absolute top-2 right-2 w-5 h-5 rounded-full bg-amber-500
                                    text-white text-xs font-bold flex items-center justify-center shadow-md">
                        </div>
                    </div>

                    {{-- Info menu --}}
                    <div class="p-3">
                        {{-- Badge kategori --}}
                        <span class="inline-block px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-semibold rounded-lg mb-1">
                            {{ $menu->kategori_nama }}
                        </span>
                        <p class="text-xs font-semibold text-gray-800 leading-tight mb-1 line-clamp-2">
                            {{ $menu->nama }}
                        </p>
                        @if($menu->deskripsi)
                            <p class="text-xs text-gray-400 leading-tight mb-2 line-clamp-1">
                                {{ $menu->deskripsi }}
                            </p>
                        @endif
                        <p class="text-xs font-bold text-amber-700 mb-2">{{ $menu->harga_format }}</p>

                        {{-- Tombol tambah / stepper --}}
                        @if(!$habis)
                            {{-- Tombol awal (belum ada di cart) --}}
                            <button onclick="tambahKeCart({{ $menu->id }}, '{{ addslashes($menu->nama) }}', {{ $menu->harga }}, {{ $menu->stok }})"
                                    id="btn-tambah-{{ $menu->id }}"
                                    class="w-full py-1.5 bg-amber-500 hover:bg-amber-600 text-white
                                           text-xs font-semibold rounded-xl transition-colors">
                                + Tambah
                            </button>

                            {{-- Stepper (muncul kalau sudah ada di cart) --}}
                            <div id="stepper-{{ $menu->id }}"
                                 class="hidden flex items-center justify-between bg-amber-50
                                        rounded-xl border border-amber-200 overflow-hidden">
                                <button onclick="kurangiCart({{ $menu->id }})"
                                        class="px-3 py-1.5 text-amber-700 hover:bg-amber-100
                                               font-bold text-sm transition-colors">
                                    −
                                </button>
                                <span id="qty-display-{{ $menu->id }}"
                                      class="text-xs font-bold text-amber-800 min-w-[20px] text-center">
                                    1
                                </span>
                                <button onclick="tambahQty({{ $menu->id }}, {{ $menu->stok }})"
                                        class="px-3 py-1.5 text-amber-700 hover:bg-amber-100
                                               font-bold text-sm transition-colors">
                                    +
                                </button>
                            </div>
                        @else
                            <button disabled
                                    class="w-full py-1.5 bg-gray-100 text-gray-400
                                           text-xs font-semibold rounded-xl cursor-not-allowed">
                                Habis
                            </button>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>

    </div>

    {{-- ============================================
         FLOATING CART BUTTON
         (muncul saat cart tidak kosong)
         ============================================ --}}
    <div id="floating-cart"
         class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-40 w-full max-w-lg px-4">
        <button onclick="bukaModalCart()"
                class="w-full bg-amber-600 hover:bg-amber-700 text-white rounded-2xl px-5 py-4
                       flex items-center justify-between shadow-xl shadow-amber-900/20
                       transition-all duration-200 active:scale-95">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-xl bg-white/20 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold">
                    <span id="cart-count">0</span> item
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold" id="cart-total-floating">Rp 0</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </button>
    </div>

    {{-- ============================================
         MODAL CART / RINGKASAN PESANAN
         ============================================ --}}
    <div id="modal-cart"
         class="fixed inset-0 z-50 hidden items-end justify-center bg-black/50"
         onclick="if(event.target===this) tutupModalCart()">
        <div class="bg-white w-full max-w-lg rounded-t-3xl max-h-[85vh] flex flex-col
                    transform transition-transform duration-300">

            {{-- Handle --}}
            <div class="flex justify-center pt-3 pb-1">
                <div class="w-10 h-1 rounded-full bg-gray-200"></div>
            </div>

            {{-- Header --}}
            <div class="px-5 py-3 flex items-center justify-between border-b border-gray-100">
                <h3 class="font-bold text-gray-900">Ringkasan Pesanan</h3>
                <button onclick="tutupModalCart()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Info pelanggan di modal --}}
            <div id="cart-info-pelanggan"
                 class="mx-5 mt-3 bg-amber-50 rounded-xl px-4 py-3 border border-amber-100">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-amber-700">
                        👤 <span id="cart-nama-display" class="font-semibold"></span>
                    </span>
                    <span class="text-amber-700">
                        🪑 Meja <span id="cart-meja-display" class="font-semibold"></span>
                    </span>
                </div>
            </div>

            {{-- Daftar item di cart --}}
            <div class="flex-1 overflow-y-auto px-5 py-3 space-y-3" id="cart-items-list">
                {{-- Diisi oleh JS --}}
            </div>

            {{-- Footer modal: total + submit --}}
            <div class="px-5 pb-6 pt-3 border-t border-gray-100 space-y-3">

                {{-- Catatan --}}
                <div id="cart-catatan-wrapper" class="hidden">
                    <p class="text-xs text-gray-400">
                        <svg class="w-4 h-4 inline mr-1 text-gray-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 5H7C5.89543 5 5 5.89543 5 7V19C5 20.1046 5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19V7C19 5.89543 18.1046 5 17 5H15" stroke="#9CA3AF" stroke-width="2" stroke-linecap="round"/>
                            <path d="M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5V7H9V5Z" stroke="#9CA3AF" stroke-width="2" stroke-linecap="round"/>
                            <path d="M9 12L11 14L15 10" stroke="#9CA3AF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span id="cart-catatan-display" class="italic"></span>
                    </p>
                </div>

                {{-- Total --}}
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700">Total</span>
                    <span class="text-lg font-bold text-amber-800" id="cart-total-modal">Rp 0</span>
                </div>

                {{-- Error --}}
                <p id="error-submit" class="text-xs text-red-500 hidden"></p>

                {{-- Tombol submit --}}
                <button id="btn-pesan"
                        onclick="submitPesanan()"
                        class="w-full py-4 bg-amber-600 hover:bg-amber-700 text-white font-bold
                               rounded-2xl transition-all active:scale-95 text-sm shadow-lg
                               shadow-amber-900/20 flex items-center justify-center gap-2">
                    <span>Kirim Pesanan</span>
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22 2L11 13" stroke="#FFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="#FFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <p class="text-xs text-center text-gray-400">
                    Pembayaran dilakukan di kasir setelah pesanan selesai
                </p>
            </div>

        </div>
    </div>

    {{-- ============================================
         MODAL SUKSES
         ============================================ --}}
    <div id="modal-sukses"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-3xl w-full max-w-sm p-8 text-center shadow-2xl">
            <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Pesanan Terkirim!</h3>
            <p class="text-sm text-gray-500 mb-2">
                Pesanan kamu sudah diterima dan sedang diproses.
            </p>
            <p class="text-xs text-gray-400 mb-6">
                Silakan tunggu di meja kamu. Pembayaran dilakukan di kasir.
            </p>
            <button onclick="resetOrder()"
                    class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold
                           rounded-2xl transition-colors text-sm">
                Pesan Lagi
            </button>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // ============================================
    // STATE & CONSTANTS
    // ============================================
    const CART_KEY   = 'kopititik_cart';
    const csrfToken  = document.querySelector('meta[name="csrf-token"]').content;
    let cart         = []; // { menu_id, nama, harga, qty, stok, catatan_item }

    // ============================================
    // INIT — restore cart dari localStorage
    // ============================================
    document.addEventListener('DOMContentLoaded', () => {
        restoreCart();
    });

    function restoreCart() {
        try {
            const saved = localStorage.getItem(CART_KEY);
            if (saved) {
                cart = JSON.parse(saved);
                cart.forEach(item => tampilkanStepper(item.menu_id, item.qty));
                updateFloatingCart();
            }
        } catch (e) {
            cart = [];
        }
    }

    function simpanCart() {
        localStorage.setItem(CART_KEY, JSON.stringify(cart));
    }

    // ============================================
    // TAMBAH KE CART
    // ============================================
    function tambahKeCart(menuId, nama, harga, stok) {
        const existing = cart.find(i => i.menu_id === menuId);

        if (existing) {
            if (existing.qty >= stok) return;
            existing.qty++;
        } else {
            cart.push({ menu_id: menuId, nama, harga, qty: 1, stok, catatan_item: null });
        }

        simpanCart();
        tampilkanStepper(menuId, cart.find(i => i.menu_id === menuId).qty);
        updateFloatingCart();
    }

    function tambahQty(menuId, stok) {
        const item = cart.find(i => i.menu_id === menuId);
        if (!item || item.qty >= stok) return;
        item.qty++;
        simpanCart();
        document.getElementById('qty-display-' + menuId).textContent = item.qty;
        document.getElementById('badge-qty-' + menuId).textContent   = item.qty;
        updateFloatingCart();
    }

    function kurangiCart(menuId) {
        const idx = cart.findIndex(i => i.menu_id === menuId);
        if (idx === -1) return;

        cart[idx].qty--;

        if (cart[idx].qty <= 0) {
            cart.splice(idx, 1);
            sembunyikanStepper(menuId);
        } else {
            document.getElementById('qty-display-' + menuId).textContent = cart[idx].qty;
            document.getElementById('badge-qty-' + menuId).textContent   = cart[idx].qty;
        }

        simpanCart();
        updateFloatingCart();
    }

    // ============================================
    // UI: tampilkan / sembunyikan stepper
    // ============================================
    function tampilkanStepper(menuId, qty) {
        const btnTambah = document.getElementById('btn-tambah-' + menuId);
        const stepper   = document.getElementById('stepper-' + menuId);
        const badge     = document.getElementById('badge-qty-' + menuId);
        const qtyEl     = document.getElementById('qty-display-' + menuId);

        if (btnTambah) btnTambah.classList.add('hidden');
        if (stepper) stepper.classList.remove('hidden');
        if (badge) { badge.textContent = qty; badge.classList.remove('hidden'); badge.classList.add('flex'); }
        if (qtyEl) qtyEl.textContent = qty;
    }

    function sembunyikanStepper(menuId) {
        const btnTambah = document.getElementById('btn-tambah-' + menuId);
        const stepper   = document.getElementById('stepper-' + menuId);
        const badge     = document.getElementById('badge-qty-' + menuId);

        if (btnTambah) btnTambah.classList.remove('hidden');
        if (stepper) stepper.classList.add('hidden');
        if (badge) { badge.classList.add('hidden'); badge.classList.remove('flex'); }
    }

    // ============================================
    // UPDATE FLOATING CART
    // ============================================
    function updateFloatingCart() {
        const totalItem = cart.reduce((sum, i) => sum + i.qty, 0);
        const totalHarga = cart.reduce((sum, i) => sum + (i.harga * i.qty), 0);

        document.getElementById('cart-count').textContent          = totalItem;
        document.getElementById('cart-total-floating').textContent = formatRupiah(totalHarga);
        document.getElementById('cart-total-modal').textContent    = formatRupiah(totalHarga);

        const floatingBtn = document.getElementById('floating-cart');
        if (totalItem > 0) {
            floatingBtn.classList.remove('hidden');
        } else {
            floatingBtn.classList.add('hidden');
        }
    }

    // ============================================
    // MODAL CART
    // ============================================
    function bukaModalCart() {
        // Populate info pelanggan
        const nama    = document.getElementById('input-nama').value.trim();
        const meja    = document.getElementById('input-meja').value.trim();
        const catatan = document.getElementById('input-catatan').value.trim();

        document.getElementById('cart-nama-display').textContent = nama || '(belum diisi)';
        document.getElementById('cart-meja-display').textContent = meja || '(belum diisi)';

        if (catatan) {
            document.getElementById('cart-catatan-display').textContent = catatan;
            document.getElementById('cart-catatan-wrapper').classList.remove('hidden');
        } else {
            document.getElementById('cart-catatan-wrapper').classList.add('hidden');
        }

        // Render item list
        const listEl = document.getElementById('cart-items-list');
        listEl.innerHTML = cart.map(item => `
            <div class="flex items-center gap-3 py-2 border-b border-gray-50 last:border-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">${item.nama}</p>
                    <p class="text-xs text-gray-400">${formatRupiah(item.harga)} / porsi</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden">
                        <button onclick="kurangiCartModal(${item.menu_id})"
                                class="px-2.5 py-1.5 text-gray-500 hover:bg-gray-50 text-sm font-bold">−</button>
                        <span class="px-2 text-xs font-bold text-gray-800 min-w-[20px] text-center"
                              id="modal-qty-${item.menu_id}">${item.qty}</span>
                        <button onclick="tambahQtyModal(${item.menu_id})"
                                class="px-2.5 py-1.5 text-gray-500 hover:bg-gray-50 text-sm font-bold">+</button>
                    </div>
                    <span class="text-xs font-bold text-amber-800 min-w-[60px] text-right"
                          id="modal-subtotal-${item.menu_id}">${formatRupiah(item.harga * item.qty)}</span>
                </div>
            </div>
        `).join('');

        document.getElementById('modal-cart').classList.remove('hidden');
        document.getElementById('modal-cart').classList.add('flex');
    }

    function tutupModalCart() {
        document.getElementById('modal-cart').classList.add('hidden');
        document.getElementById('modal-cart').classList.remove('flex');
    }

    // Tambah/kurang dari dalam modal
    function tambahQtyModal(menuId) {
        const item = cart.find(i => i.menu_id === menuId);
        if (!item || item.qty >= item.stok) return;
        item.qty++;
        simpanCart();
        document.getElementById('modal-qty-' + menuId).textContent      = item.qty;
        document.getElementById('modal-subtotal-' + menuId).textContent = formatRupiah(item.harga * item.qty);
        // Update badge & stepper di card
        document.getElementById('qty-display-' + menuId).textContent = item.qty;
        document.getElementById('badge-qty-' + menuId).textContent   = item.qty;
        updateFloatingCart();
    }

    function kurangiCartModal(menuId) {
        kurangiCart(menuId);
        // Re-render modal jika masih ada item, tutup jika kosong
        if (cart.length === 0) {
            tutupModalCart();
        } else {
            // Update qty di modal
            const item = cart.find(i => i.menu_id === menuId);
            if (item) {
                document.getElementById('modal-qty-' + menuId).textContent      = item.qty;
                document.getElementById('modal-subtotal-' + menuId).textContent = formatRupiah(item.harga * item.qty);
            } else {
                // Item dihapus dari cart, hapus row-nya dari modal
                bukaModalCart(); // Re-render modal
            }
        }
    }

    // ============================================
    // SUBMIT PESANAN
    // ============================================
    function submitPesanan() {
        const nama    = document.getElementById('input-nama').value.trim();
        const meja    = document.getElementById('input-meja').value.trim();
        const catatan = document.getElementById('input-catatan').value.trim();
        const errorEl = document.getElementById('error-submit');
        const btn     = document.getElementById('btn-pesan');

        errorEl.classList.add('hidden');

        // Validasi
        if (!nama) {
            errorEl.textContent = 'Nama harus diisi sebelum memesan.';
            errorEl.classList.remove('hidden');
            tutupModalCart();
            document.getElementById('input-nama').focus();
            document.getElementById('input-nama').classList.add('border-red-400');
            return;
        }

        if (!meja) {
            errorEl.textContent = 'Nomor meja harus diisi sebelum memesan.';
            errorEl.classList.remove('hidden');
            tutupModalCart();
            document.getElementById('input-meja').focus();
            document.getElementById('input-meja').classList.add('border-red-400');
            return;
        }

        if (cart.length === 0) {
            errorEl.textContent = 'Keranjang masih kosong.';
            errorEl.classList.remove('hidden');
            return;
        }

        btn.textContent  = 'Mengirim...';
        btn.disabled     = true;

        const payload = {
            nama_pelanggan: nama,
            nomor_meja:     meja,
            catatan:        catatan || null,
            items: cart.map(i => ({
                menu_id:      i.menu_id,
                qty:          i.qty,
                catatan_item: i.catatan_item,
            })),
        };

        fetch('{{ route('order.store') }}', {
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
                tutupModalCart();
                // Tampilkan modal sukses
                document.getElementById('modal-sukses').classList.remove('hidden');
                document.getElementById('modal-sukses').classList.add('flex');
            } else {
                errorEl.textContent = data.message;
                errorEl.classList.remove('hidden');
            }
        })
        .catch(() => {
            errorEl.textContent = 'Gagal terhubung ke server. Periksa koneksi internet kamu.';
            errorEl.classList.remove('hidden');
        })
        .finally(() => {
            btn.innerHTML = '<span>Kirim Pesanan</span><svg class="w-5 h-5 ml-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22 2L11 13" stroke="#FFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="#FFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            btn.disabled    = false;
        });
    }

    // ============================================
    // RESET SETELAH SUKSES
    // ============================================
    function resetOrder() {
        // Clear cart
        cart = [];
        localStorage.removeItem(CART_KEY);

        // Reset semua stepper ke tombol tambah
        document.querySelectorAll('[id^="stepper-"]').forEach(el => {
            const menuId = el.id.replace('stepper-', '');
            sembunyikanStepper(menuId);
        });

        // Reset form
        document.getElementById('input-nama').value    = '';
        document.getElementById('input-meja').value    = '';
        document.getElementById('input-catatan').value = '';

        updateFloatingCart();

        // Tutup modal sukses
        document.getElementById('modal-sukses').classList.add('hidden');
        document.getElementById('modal-sukses').classList.remove('flex');

        // Scroll ke atas
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ============================================
    // FILTER KATEGORI
    // ============================================
    function filterKategori(kategoriId) {
        // Update active tab
        document.querySelectorAll('.tab-kategori').forEach(tab => {
            tab.classList.remove('bg-amber-500', 'text-white', 'border-amber-500');
            tab.classList.add('bg-white', 'text-gray-600', 'border-gray-200');
        });
        const activeTab = document.getElementById('tab-' + kategoriId);
        if (activeTab) {
            activeTab.classList.add('bg-amber-500', 'text-white', 'border-amber-500');
            activeTab.classList.remove('bg-white', 'text-gray-600', 'border-gray-200');
        }

        // Filter menu cards
        document.querySelectorAll('.menu-card').forEach(card => {
            if (kategoriId === 'all') {
                card.classList.remove('hidden');
            } else {
                const cardKategori = card.getAttribute('data-kategori-id');
                if (cardKategori === kategoriId.toString()) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            }
        });
    }

    // ============================================
    // HELPER
    // ============================================
    function formatRupiah(angka) {
        return 'Rp ' + Number(angka).toLocaleString('id-ID');
    }

    // Reset border merah saat user mulai ketik
    ['input-nama', 'input-meja'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', function () {
            this.classList.remove('border-red-400');
        });
    });
</script>

<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    .line-clamp-1 { overflow: hidden; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; }
    .line-clamp-2 { overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
</style>
@endpush