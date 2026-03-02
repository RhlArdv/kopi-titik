<?php

namespace App\Http\Controllers;

use App\Models\KategoriMenu;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        // Tidak perlu DataTables lagi — card view seperti Stok
        $kategoris = \App\Models\KategoriMenu::with(['menus' => function ($q) {
            $q->orderBy('nama');
        }])
        ->orderBy('nama')
        ->get();

        return view('menu.index', compact('kategoris'));
    }

    // public function dataTable()
    // {
    //     $builder = Menu::with('kategori')
    //         ->select('menu.*')
    //         ->orderBy('kategori_id')
    //         ->orderBy('nama');

    //     return DataTables::of($builder)
    //         ->addIndexColumn()
    //         ->addColumn('gambar', function ($row) {
    //             $url = $row->gambar_url;
    //             return '<img src="' . $url . '" alt="' . e($row->nama) . '"
    //                         class="w-12 h-12 object-cover rounded-lg border border-gray-200">';
    //         })
    //         ->addColumn('kategori', function ($row) {
    //             return $row->kategori->nama ?? '-';
    //         })
    //         ->addColumn('harga', function ($row) {
    //             return $row->harga_format;
    //         })
    //         ->addColumn('stok', function ($row) {
    //             if ($row->stok == 0) {
    //                 return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Habis</span>';
    //             } elseif ($row->stok <= 5) {
    //                 return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">' . $row->stok . ' tersisa</span>';
    //             }
    //             return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">' . $row->stok . '</span>';
    //         })
    //         ->addColumn('status', function ($row) {
    //             if ($row->is_aktif) {
    //                 return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Aktif</span>';
    //             }
    //             return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Nonaktif</span>';
    //         })
    //         ->addColumn('aksi', function ($row) {
    //             return '
    //                 <div class="flex items-center gap-2">
    //                     <button
    //                         onclick="editMenu(' . $row->id . ')"
    //                         class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg
    //                                bg-amber-50 text-amber-700 hover:bg-amber-100 transition-colors"
    //                     >
    //                         Edit
    //                     </button>
    //                     <button
    //                         onclick="hapusMenu(' . $row->id . ', \'' . e($row->nama) . '\')"
    //                         class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg
    //                                bg-red-50 text-red-700 hover:bg-red-100 transition-colors"
    //                     >
    //                         Hapus
    //                     </button>
    //                 </div>
    //             ';
    //         })
    //         ->rawColumns(['gambar', 'stok', 'status', 'aksi'])
    //         ->make(true);
    // }

    public function create()
    {
        $kategoris = KategoriMenu::orderBy('urutan')->get();
        return view('menu.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_id' => 'required|exists:kategori_menu,id',
            'nama'        => 'required|string|max:255',
            'deskripsi'   => 'nullable|string|max:500',
            'harga'       => 'required|numeric|min:0',
            'stok'        => 'required|integer|min:0',
            'gambar'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_aktif'    => 'nullable|boolean',
        ]);

        $gambarPath = null;

        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('menu', 'public');
        }

        Menu::create([
            'kategori_id' => $request->kategori_id,
            'nama'        => $request->nama,
            'deskripsi'   => $request->deskripsi,
            'harga'       => $request->harga,
            'stok'        => $request->stok,
            'gambar'      => $gambarPath,
            'is_aktif'    => $request->boolean('is_aktif', true),
        ]);

        return redirect()->route('menu.index')
            ->with('success', 'Menu "' . $request->nama . '" berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $menu      = Menu::findOrFail($id);
        $kategoris = KategoriMenu::orderBy('urutan')->get();

        return view('menu.edit', compact('menu', 'kategoris'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kategori_id' => 'required|exists:kategori_menu,id',
            'nama'        => 'required|string|max:255',
            'deskripsi'   => 'nullable|string|max:500',
            'harga'       => 'required|numeric|min:0',
            'stok'        => 'required|integer|min:0',
            'gambar'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_aktif'    => 'nullable|boolean',
        ]);

        $menu       = Menu::findOrFail($id);
        $gambarPath = $menu->gambar;

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($gambarPath) {
                Storage::disk('public')->delete($gambarPath);
            }
            $gambarPath = $request->file('gambar')->store('menu', 'public');
        }

        // Hapus gambar jika user klik tombol "Hapus Gambar"
        if ($request->boolean('hapus_gambar') && $gambarPath) {
            Storage::disk('public')->delete($gambarPath);
            $gambarPath = null;
        }

        $menu->update([
            'kategori_id' => $request->kategori_id,
            'nama'        => $request->nama,
            'deskripsi'   => $request->deskripsi,
            'harga'       => $request->harga,
            'stok'        => $request->stok,
            'gambar'      => $gambarPath,
            'is_aktif'    => $request->boolean('is_aktif', true),
        ]);

        return redirect()->route('menu.index')
            ->with('success', 'Menu "' . $menu->nama . '" berhasil diperbarui.');
    }

    public function show($id)
    {
        $menu = Menu::with('kategori')->findOrFail($id);
        return view('menu.show', compact('menu'));
    }

    public function destroy($id)
    {
        try {
            $menu = Menu::findOrFail($id);

            if ($menu->gambar) {
                Storage::disk('public')->delete($menu->gambar);
            }

            $menu->delete();

            return response()->json([
                'success' => true,
                'message' => 'Menu "' . $menu->nama . '" berhasil dihapus.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus menu. Silakan coba lagi.',
            ], 500);
        }
    }
}