@extends('layouts.app')

@section('title', 'Edit Menu')

@section('page-header')
    <div class="flex items-center gap-3">
        <a href="{{ route('menu.index') }}"
           class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-900">Edit Menu</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ $menu->nama }}</p>
        </div>
    </div>
@endsection

@section('content')

    <form action="{{ route('menu.update', $menu->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ============ KOLOM KIRI: Gambar ============ --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-800 mb-4">Foto Menu</h3>

                    {{-- Preview gambar --}}
                    <div id="preview-container"
                         class="relative w-full aspect-square rounded-xl border-2 border-dashed border-gray-200
                                bg-gray-50 flex flex-col items-center justify-center overflow-hidden mb-3
                                hover:border-amber-400 transition-colors cursor-pointer"
                         onclick="document.getElementById('input-gambar').click()">

                        {{-- Placeholder (tampil jika tidak ada gambar) --}}
                        <div id="placeholder-gambar" class="{{ $menu->gambar ? 'hidden' : '' }} text-center">
                            <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-xs text-gray-400">Klik untuk upload foto</p>
                        </div>

                        {{-- Gambar saat ini / preview baru --}}
                        <img id="preview-gambar"
                             src="{{ $menu->gambar ? asset('storage/' . $menu->gambar) : '' }}"
                             alt="{{ $menu->nama }}"
                             class="{{ $menu->gambar ? '' : 'hidden' }} absolute inset-0 w-full h-full object-cover">
                    </div>

                    <input type="file"
                           id="input-gambar"
                           name="gambar"
                           accept="image/jpeg,image/png,image/jpg,image/webp"
                           class="hidden"
                           onchange="previewGambar(this)">

                    @error('gambar')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror

                    {{-- Tombol hapus gambar (hanya tampil jika ada gambar) --}}
                    @if($menu->gambar)
                        <div id="wrapper-hapus-gambar" class="mt-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox"
                                       name="hapus_gambar"
                                       value="1"
                                       id="cb-hapus-gambar"
                                       class="rounded border-gray-300 text-red-500 focus:ring-red-400"
                                       onchange="toggleHapusGambar(this)">
                                <span class="text-xs text-red-500 font-medium">Hapus foto ini</span>
                            </label>
                        </div>
                    @endif

                </div>
            </div>

            {{-- ============ KOLOM KANAN: Detail Menu ============ --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Info Dasar --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-800 mb-4">Informasi Menu</h3>

                    <div class="space-y-4">

                        {{-- Kategori --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Kategori <span class="text-red-500">*</span>
                            </label>
                            <select name="kategori_id"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                           focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400
                                           @error('kategori_id') border-red-400 @enderror">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoris as $kategori)
                                    <option value="{{ $kategori->id }}"
                                            {{ (old('kategori_id', $menu->kategori_id)) == $kategori->id ? 'selected' : '' }}>
                                        {{ $kategori->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kategori_id')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nama Menu --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Nama Menu <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="nama"
                                   value="{{ old('nama', $menu->nama) }}"
                                   placeholder="Contoh: Kopi Susu Titik"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                          focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400
                                          @error('nama') border-red-400 @enderror">
                            @error('nama')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Deskripsi --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Deskripsi
                                <span class="text-gray-400 font-normal">(opsional)</span>
                            </label>
                            <textarea name="deskripsi"
                                      rows="3"
                                      maxlength="500"
                                      placeholder="Deskripsi singkat menu..."
                                      class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                             focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400
                                             resize-none @error('deskripsi') border-red-400 @enderror">{{ old('deskripsi', $menu->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- Harga, Stok, Status --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-800 mb-4">Harga & Stok</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- Harga --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Harga (Rp) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">Rp</span>
                                <input type="number"
                                       name="harga"
                                       value="{{ old('harga', (int)$menu->harga) }}"
                                       min="0"
                                       step="500"
                                       class="w-full border border-gray-200 rounded-lg pl-9 pr-3 py-2.5 text-sm
                                              focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400
                                              @error('harga') border-red-400 @enderror">
                            </div>
                            @error('harga')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Stok --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Stok Saat Ini <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                   name="stok"
                                   value="{{ old('stok', $menu->stok) }}"
                                   min="0"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                          focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400
                                          @error('stok') border-red-400 @enderror">
                            <p class="text-xs text-gray-400 mt-1 flex items-start gap-1">
                                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 18H15" stroke="#9CA3AF" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M10 22H14" stroke="#9CA3AF" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M12 2C7.5 2 5 5.5 5 8C5 10.5 7 11 7 14C7 14.5 7.5 15 8 15H16C16.5 15 17 14.5 17 14C17 11 19 10.5 19 8C19 5.5 16.5 2 12 2Z" fill="#FBBF24" stroke="#F59E0B" stroke-width="1.5"/>
                                    <path d="M12 6V12M9.5 9H14.5" stroke="#FFF" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                <span>Untuk isi ulang stok harian, gunakan menu
                                <a href="{{ route('stok.index') }}" class="text-amber-600 hover:underline">Stok Menu</a>.</span>
                            </p>
                            @error('stok')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    {{-- Status Aktif --}}
                    <div class="mt-4 flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="is_aktif" value="0">
                            <input type="checkbox"
                                   name="is_aktif"
                                   value="1"
                                   {{ old('is_aktif', $menu->is_aktif ? '1' : '0') == '1' ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-10 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer
                                        peer-checked:after:translate-x-full peer-checked:after:border-white
                                        after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                        after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all
                                        peer-checked:bg-amber-500"></div>
                        </label>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Menu Aktif</p>
                            <p class="text-xs text-gray-400">Menu nonaktif tidak tampil di halaman pemesanan</p>
                        </div>
                    </div>

                </div>

                {{-- Tombol Aksi --}}
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('menu.index') }}"
                       class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200
                              hover:bg-gray-50 rounded-lg transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 text-sm font-medium text-white bg-amber-500
                                   hover:bg-amber-600 rounded-lg transition-colors">
                        Simpan Perubahan
                    </button>
                </div>

            </div>
        </div>

    </form>

@endsection

@push('scripts')
<script>
    function previewGambar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('preview-gambar').src = e.target.result;
                document.getElementById('preview-gambar').classList.remove('hidden');
                document.getElementById('placeholder-gambar').classList.add('hidden');

                // Uncheck hapus gambar jika upload gambar baru
                const cb = document.getElementById('cb-hapus-gambar');
                if (cb) cb.checked = false;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function toggleHapusGambar(checkbox) {
        const preview = document.getElementById('preview-gambar');
        const placeholder = document.getElementById('placeholder-gambar');

        if (checkbox.checked) {
            preview.classList.add('opacity-30');
            preview.classList.add('grayscale');
        } else {
            preview.classList.remove('opacity-30');
            preview.classList.remove('grayscale');
        }
    }
</script>
@endpush