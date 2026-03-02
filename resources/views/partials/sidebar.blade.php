    {{--
        SIDEBAR - Fixed kiri, permission-aware
        Setiap item menu dicek dengan hasPermission() sebelum ditampilkan.
        Alpine.js dipakai untuk toggle mobile sidebar via x-data di layouts/app.blade.php
    --}}

    <aside
        class="fixed inset-y-0 left-0 z-30 w-64 bg-gray-900 text-gray-100 flex flex-col
            transform transition-transform duration-200 ease-in-out
            -translate-x-full lg:translate-x-0"
        :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }"
        x-cloak
    >
        {{-- ============================================
            LOGO / BRAND
            ============================================ --}}
        <div class="flex items-center gap-3 px-5 py-5 border-b border-gray-700/50">
            <div class="w-9 h-9 rounded-xl bg-amber-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M2 21v-2h2V7a1 1 0 011-1h14a1 1 0 011 1v2h1a2 2 0 010 4h-1v5h2v2H2zm4-2h10v-5H6v5zm0-7h10V8H6v4zm12-2v-2h1a.5.5 0 000 1h-1v1z"/>
                </svg>
            </div>
            <div>
                <p class="font-bold text-white leading-none">TEST</p>
                <p class="text-xs text-gray-400 leading-none mt-0.5">Sistem Informasi</p>
            </div>

            {{-- Tombol tutup sidebar (mobile) --}}
            <button
                @click="sidebarOpen = false"
                class="ml-auto lg:hidden text-gray-400 hover:text-white"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- ============================================
            NAVIGATION MENU
            ============================================ --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">

            {{-- Dashboard --}}
            @if(auth()->user()->hasPermission('view_dashboard'))
                <x-sidebar-item
                    route="dashboard"
                    label="Dashboard"
                    icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'
                />
            @endif

            {{-- ---- Grup: Pesanan ---- --}}
            @if(auth()->user()->hasPermission('view_pesanan') || auth()->user()->hasPermission('konfirmasi_pembayaran'))
                <p class="px-3 pt-4 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pesanan</p>
            @endif

            @if(auth()->user()->hasPermission('view_pesanan'))
                <x-sidebar-item
                    route="pesanan.index"
                    label="Pesanan Masuk"
                    icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>'
                />
            @endif

            {{-- @if(auth()->user()->hasPermission('konfirmasi_pembayaran'))
                <x-sidebar-item
                    route="pembayaran.index"
                    label="Pembayaran"
                    icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>'
                />
            @endif --}}

            @if(auth()->user()->hasPermission('view_histori_pesanan'))
                <x-sidebar-item
                    route="pesanan.histori"
                    label="Histori Pesanan"
                    icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                />
            @endif

            {{-- ---- Grup: Menu & Stok ---- --}}
            @if(auth()->user()->hasPermission('view_menu') || auth()->user()->hasPermission('view_stok'))
                <p class="px-3 pt-4 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Produk</p>
            @endif

            @if(auth()->user()->hasPermission('view_menu'))
                <x-sidebar-item
                    route="menu.index"
                    label="Kelola Menu"
                    icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>'
                />
            @endif

            @if(auth()->user()->hasPermission('view_kategori'))
                <x-sidebar-item
                    route="kategori.index"
                    label="Kategori Menu"
                    icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>'
                />
            @endif

            @if(auth()->user()->hasPermission('view_stok'))
                <x-sidebar-item
                    route="stok.index"
                    label="Stok Menu"
                    icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'
                />
            @endif

            {{-- ---- Grup: Laporan ---- --}}
            @if(auth()->user()->hasPermission('view_laporan'))
                <p class="px-3 pt-4 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Laporan</p>

                <x-sidebar-item
                    route="laporan.index"
                    label="Laporan Transaksi"
                    icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'
                />
            @endif

            {{-- ---- Grup: Pengaturan (Admin only) ---- --}}
            @if(auth()->user()->hasPermission('view_users') || auth()->user()->hasPermission('view_roles'))
                <p class="px-3 pt-4 pb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pengaturan</p>
            @endif

            @if(auth()->user()->hasPermission('view_users'))
                <x-sidebar-item
                    route="users.index"
                    label="Kelola User"
                    icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>'
                />
            @endif 

            @if(auth()->user()->hasPermission('view_roles'))
                <x-sidebar-item
                    route="roles.index"
                    label="Role & Permission"
                    icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>'
                />
            @endif

        </nav>

        {{-- ============================================
            USER INFO (bawah sidebar)
            ============================================ --}}
        <div class="border-t border-gray-700/50 px-4 py-4">
            <div class="flex items-center gap-3">
                {{-- Avatar inisial --}}
                <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ auth()->user()->role?->display_name ?? 'Tanpa Role' }}</p>
                </div>
                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-red-400 transition-colors" title="Logout">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

    </aside>