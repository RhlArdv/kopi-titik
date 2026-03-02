<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Kopi Titik</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

    @stack('styles')

    <style>
        * { font-family: 'DM Sans', sans-serif; }
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

        .nav-item-active::before {
            content: '';
            position: absolute;
            left: 0; top: 6px; bottom: 6px;
            width: 3px;
            background: #4f46e5;
            border-radius: 0 3px 3px 0;
        }

        main { animation: fadeUp 0.25s ease both; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        #sidebar { transition: transform 0.25s cubic-bezier(.4,0,.2,1); }

        .brand-font { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-[#F3F4F6] text-gray-800 antialiased" x-data="{ sidebarOpen: false }">

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen"
         x-transition.opacity
         @click="sidebarOpen = false"
         class="fixed inset-0 z-20 bg-black/30 backdrop-blur-sm lg:hidden"></div>

    <div class="flex min-h-screen">

        {{-- ================================================
             SIDEBAR
             ================================================ --}}
        <aside id="sidebar"
               class="fixed inset-y-0 left-0 z-30 w-[260px] bg-white border-r border-gray-100
                      flex flex-col -translate-x-full lg:translate-x-0"
               :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }">

            {{-- Brand --}}
            <div class="flex items-center justify-between px-5 h-[60px] border-b border-gray-100 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg overflow-hidden flex-shrink-0 bg-indigo-50 flex items-center justify-center">
                        <img src="{{ asset('assets/img/logo.png') }}" alt="Kopi Titik Logo" class="w-full h-full object-contain">
                    </div>
                    <span class="brand-font font-bold text-gray-900 text-[15px] tracking-tight">Kopi Titik V2</span>
                </div>
                <button @click="sidebarOpen = false"
                        class="lg:hidden p-1 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">

                <p class="px-3 pt-1 pb-2 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Menu Utama</p>

                @if(auth()->user()->hasPermission('view_dashboard'))
                @php $activeDashboard = request()->routeIs('dashboard'); @endphp
                <a href="{{ route('dashboard') }}"
                   class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium
                          transition-all {{ $activeDashboard ? 'nav-item-active bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-[17px] h-[17px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $activeDashboard ? '2.2' : '1.8' }}"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>
                @endif

                @if(auth()->user()->hasPermission('view_pesanan') || auth()->user()->hasPermission('view_histori_pesanan'))
                    <p class="px-3 pt-4 pb-2 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Pesanan</p>
                @endif

                @if(auth()->user()->hasPermission('view_pesanan'))
                @php
                    $activePesanan = request()->routeIs('pesanan.index');
                    $countMenunggu = \App\Models\Pesanan::whereDate('created_at', today())->where('status_pesanan','menunggu')->count();
                @endphp
                <a href="{{ route('pesanan.index') }}"
                   class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium
                          transition-all {{ $activePesanan ? 'nav-item-active bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-[17px] h-[17px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $activePesanan ? '2.2' : '1.8' }}"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span class="flex-1">Pesanan Masuk</span>
                    @if($countMenunggu > 0)
                        <span class="bg-indigo-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center leading-tight">
                            {{ $countMenunggu }}
                        </span>
                    @endif
                </a>
                @endif

                @if(auth()->user()->hasPermission('view_histori_pesanan'))
                @php $activeHistori = request()->routeIs('pesanan.histori'); @endphp
                <a href="{{ route('pesanan.histori') }}"
                   class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium
                          transition-all {{ $activeHistori ? 'nav-item-active bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-[17px] h-[17px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $activeHistori ? '2.2' : '1.8' }}"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Histori Pesanan
                </a>
                @endif

                @if(auth()->user()->hasPermission('view_menu') || auth()->user()->hasPermission('view_stok') || auth()->user()->hasPermission('view_kategori'))
                    <p class="px-3 pt-4 pb-2 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Produk</p>
                @endif

                @if(auth()->user()->hasPermission('view_menu'))
                @php $activeMenu = request()->routeIs('menu.*'); @endphp
                <a href="{{ route('menu.index') }}"
                   class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium
                          transition-all {{ $activeMenu ? 'nav-item-active bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-[17px] h-[17px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $activeMenu ? '2.2' : '1.8' }}" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    Kelola Menu
                </a>
                @endif

                @if(auth()->user()->hasPermission('view_kategori'))
                @php $activeKategori = request()->routeIs('kategori.*'); @endphp
                <a href="{{ route('kategori.index') }}"
                   class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium
                          transition-all {{ $activeKategori ? 'nav-item-active bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-[17px] h-[17px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $activeKategori ? '2.2' : '1.8' }}"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Kategori Menu
                </a>
                @endif

                @if(auth()->user()->hasPermission('view_stok'))
                @php $activeStok = request()->routeIs('stok.*'); @endphp
                <a href="{{ route('stok.index') }}"
                   class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium
                          transition-all {{ $activeStok ? 'nav-item-active bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-[17px] h-[17px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $activeStok ? '2.2' : '1.8' }}"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Stok Menu
                </a>
                @endif

                @if(auth()->user()->hasPermission('view_laporan'))
                    <p class="px-3 pt-4 pb-2 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Laporan</p>
                    @php $activeLaporan = request()->routeIs('laporan.*'); @endphp
                    <a href="{{ route('laporan.index') }}"
                    class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium
                            transition-all {{ $activeLaporan ? 'nav-item-active bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-[17px] h-[17px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $activeLaporan ? '2.2' : '1.8' }}"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Laporan Transaksi
                    </a>
                @endif

                @if(auth()->user()->hasPermission('view_users') || auth()->user()->hasPermission('view_roles'))
                    <p class="px-3 pt-4 pb-2 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Pengaturan</p>
                @endif

                @if(auth()->user()->hasPermission('view_users'))
                @php $activeUsers = request()->routeIs('users.*'); @endphp
                <a href="{{ route('users.index') }}"
                   class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium
                          transition-all {{ $activeUsers ? 'nav-item-active bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-[17px] h-[17px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $activeUsers ? '2.2' : '1.8' }}"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Kelola User
                </a>
                @endif

                @if(auth()->user()->hasPermission('view_roles'))
                @php $activeRoles = request()->routeIs('roles.*'); @endphp
                <a href="{{ route('roles.index') }}"
                   class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium
                          transition-all {{ $activeRoles ? 'nav-item-active bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-[17px] h-[17px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $activeRoles ? '2.2' : '1.8' }}"
                              d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Role & Permission
                </a>
                @endif

            </nav>

            {{-- User card bawah --}}
            <div class="flex-shrink-0 border-t border-gray-100 p-3">
                <div class="flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-gray-50 transition-colors">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-blue-600
                                flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-semibold text-gray-800 truncate leading-tight">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-gray-400 truncate">{{ auth()->user()->role?->display_name ?? 'Tanpa Role' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Logout"
                                class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50
                                       rounded-lg transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

        </aside>

        {{-- ================================================
             MAIN CONTENT
             ================================================ --}}
        <div class="flex flex-col flex-1 min-w-0 lg:pl-[260px]">

            {{-- NAVBAR --}}
            <header class="sticky top-0 z-10 h-[60px] bg-white/90 backdrop-blur-md
                           border-b border-gray-100 flex items-center px-5 gap-4">

                {{-- Hamburger mobile --}}
                <button @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div class="flex-1"></div>

                {{-- Notifikasi bell --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="relative w-9 h-9 flex items-center justify-center rounded-xl
                                   text-gray-500 hover:bg-gray-100 transition-colors">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @php $notifCount = \App\Models\Pesanan::whereDate('created_at', today())->where('status_pesanan','menunggu')->count(); @endphp
                        @if($notifCount > 0)
                            <span class="absolute top-1.5 right-1.5 w-[7px] h-[7px] bg-red-500
                                         rounded-full border-2 border-white"></span>
                        @endif
                    </button>

                    <div x-show="open"
                         @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 top-full mt-2 w-72 bg-white rounded-2xl
                                border border-gray-100 shadow-xl shadow-gray-200/60 overflow-hidden z-50">
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-800">Notifikasi</p>
                            @if($notifCount > 0)
                                <span class="text-[11px] bg-indigo-100 text-indigo-700 font-semibold px-2 py-0.5 rounded-full">
                                    {{ $notifCount }} baru
                                </span>
                            @endif
                        </div>
                        <div class="p-2 max-h-64 overflow-y-auto">
                            @if($notifCount > 0)
                                <a href="{{ route('pesanan.index') }}"
                                   @click="open = false"
                                   class="flex items-start gap-3 p-3 rounded-xl hover:bg-indigo-50 transition-colors">
                                    <div class="w-8 h-8 rounded-xl bg-indigo-100 flex items-center
                                                justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-semibold text-gray-800">{{ $notifCount }} pesanan menunggu</p>
                                        <p class="text-xs text-gray-400 mt-0.5">Klik untuk lihat pesanan masuk</p>
                                    </div>
                                </a>
                            @else
                                <div class="py-8 text-center">
                                    <p class="text-sm text-gray-400">Tidak ada notifikasi baru</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Divider --}}
                <div class="w-px h-6 bg-gray-200"></div>

                {{-- Avatar + nama --}}
                <div class="flex items-center gap-2.5">
                    <div class="text-right hidden md:block">
                        <p class="text-[13px] font-semibold text-gray-800 leading-none">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-gray-400 leading-none mt-0.5">{{ auth()->user()->role?->display_name ?? '-' }}</p>
                    </div>
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-blue-600
                                flex items-center justify-center text-white text-xs font-bold cursor-pointer
                                shadow-sm shadow-indigo-200">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                </div>

            </header>

            {{-- KONTEN --}}
            <main class="flex-1 p-5 lg:p-6">
                @hasSection('page-header')
                    <div class="mb-5">@yield('page-header')</div>
                @endif

                @include('partials.alert')

                @yield('content')
            </main>

            <footer class="px-6 py-3 text-center text-xs text-gray-300 border-t border-gray-100 bg-white">
                © {{ date('Y') }} Kopi Titik — Sistem Informasi Reservasi Menu
            </footer>

        </div>
    </div>

    @stack('scripts')
</body>
</html>