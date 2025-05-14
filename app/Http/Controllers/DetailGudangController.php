<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\SatuanBerat;
use App\Models\DetailGudang;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DetailGudangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $detailGudang = DetailGudang::with('barang', 'gudang', 'satuanBerat')->where('id_gudang', auth()->user()->gudang->id)->get();

        return view('barangs.index', [
            'detailGudang' => $detailGudang,
            'message' => 'Data Detail Gudang retrieved successfully',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $barangs = Barang::all();
        $gudang = GudangDanToko::all();
        $satuanBerat = SatuanBerat::all();

        return view('barangs.create', [
            'barangs' => $barangs,
            'gudang' => $gudang,
            'satuanBerat' => $satuanBerat,
            'message' => 'Form Tambah Barang Gudang',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_barang' => 'required|exists:barangs,id',
            'id_gudang' => 'required|exists:gudang_dan_tokos,id',
            'id_satuan_berat' => 'required|exists:satuan_berats,id',
            'jumlah_stok' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                DetailGudang::create($validated);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return redirect()->route('barangs.index')->with('success', 'Data Detail Gudang berhasil ditambahkan');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Gagal menambahkan Data Barang Gudang. Silakan coba lagi.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $detailGudang = DetailGudang::with('barang', 'gudang', 'satuanBerat')->findOrFail($id);
            return view('barangs.show', [
                'detailGudang' => $detailGudang,
                'message' => "Data Barang Gudang dengan ID: {$id}",
            ]);
        } catch (\Throwable $th) {
            return redirect()->route('barangs.index')->with('error', "Data Barang Gudang dengan ID: {$id} tidak ditemukan");
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $detailGudang = DetailGudang::findOrFail($id);
            $barangs = Barang::all();
            $gudang = GudangDanToko::all();
            $satuanBerat = SatuanBerat::all();

            return view('barangs.edit', [
                'detailGudang' => $detailGudang,
                'barangs' => $barangs,
                'gudang' => $gudang,
                'satuanBerat' => $satuanBerat,
                'message' => 'Form Edit Barang Gudang',
            ]);
        } catch (\Throwable $th) {
            return redirect()->route('barangs.index')->with('error', "Data Barang Gudang dengan ID: {$id} tidak ditemukan");
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'id_barang' => 'required|exists:barangs,id',
            'id_gudang' => 'required|exists:gudang_dan_tokos,id',
            'jumlah_stok' => 'required|integer|min:1',
            'id_satuan_berat' => 'required|exists:satuan_berats,id',
            'stok_opname' => 'nullable|integer|min:0|max:1', // Ditambahkan nullable agar tidak selalu wajib diisi
        ]);

        try {
            $detailGudang = DetailGudang::findOrFail($id);
            DB::transaction(function () use ($validated, $detailGudang) {
                $detailGudang->update($validated);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return redirect()->route('barangs.index')->with('success', "Data Barang Gudang dengan ID: {$detailGudang->id} berhasil diperbarui");
        } catch (ModelNotFoundException $e) {
            return redirect()->route('barangs.index')->with('error', "Data Barang Gudang dengan ID: {$id} tidak ditemukan");
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', "Gagal memperbarui Data Barang Gudang dengan ID: {$id}. Silakan coba lagi.");
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $barangGudang = DetailGudang::findOrFail($id);
            DB::transaction(function () use ($barangGudang) {
                $barangGudang->update(['flag' => 0]);
            });

            return redirect()->route('barangs.index')->with('success', "Data 
            Barang Gudang dengan ID: {$id} berhasil dihapus.");
        } catch (ModelNotFoundException $e) {
            return redirect()->route('barangs.index')->with('error', "Data 
            Barang Gudang dengan ID: {$id} tidak ditemukan.");
        } catch (\Throwable $th) {
            return redirect()->route('barangs.index')->with('error', "Terjadi kesalahan saat menghapus Data Barang Gudang dengan ID: {$id}.");
        }
    }
}