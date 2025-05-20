<?php

namespace App\Http\Controllers;

use App\Models\Kurir;
use App\Models\Barang;
use App\Models\Status;
use App\Models\SatuanBerat;
use App\Models\DetailGudang;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use App\Models\JenisPenerimaan;
use App\Models\PusatKeSupplier;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class PusatKeSupplierController extends Controller
{
    public function index()
    {
        try {
            $pusatKeSuppliers = PusatKeSupplier::with([
                'supplier',
                'pusat',
                'barang',
                'kurir',
                'satuanBerat',
                'status'
            ])->get();

            return response()->json([
                'status' => true,
                'message' => 'Data Pusat Ke Supplier',
                'data' => $pusatKeSuppliers
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menampilkan data Pusat Ke Supplier",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function create()
    {
        try {
            $barangs = Barang::all();
            $supplier = GudangDanToko::whereIn('kategori_bangunan', [1])->get();
            $status = Status::where('id', 1)->get();
            $kurir = Kurir::all();
            $pusat = GudangDanToko::where('id', 1)->get();
            $satuanBerat = SatuanBerat::all();

            return response()->json([
                'status' => true,
                'message' => 'Data Barang, Jenis Penerimaan tertentu, Supplier, Pusat, dan informasi pendukung lainnya',
                'data' => [
                    'barangs' => $barangs,
                    'supplier' => $supplier,
                    'pusat' => $pusat,
                    'satuanBerat' => $satuanBerat,
                    'status' => $status,
                    'kurir' => $kurir,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
               'kode'=> 'required|string|max:255',
               'id_supplier' => 'required|exists:gudang_dan_tokos,id',
               'id_pusat' => 'required|exists:gudang_dan_tokos,id',
               'id_barang' => 'required|exists:barangs,id',
               'id_satuan_berat' => 'required|exists:satuan_berats,id',
               'id_kurir' => 'required|exists:kurirs,id',
               'id_status' => 'required|exists:statuses,id',
               'berat_satuan_barang' => 'required|numeric|min:1',
               'jumlah_barang' => 'required|integer|min:1',
               'tanggal' => 'required|date',
            ]);

            $data = PusatKeSupplier::create($validated);

            return response()->json([
                'status' => true,
                'message' => 'Data Pusat ke Supplier berhasil disimpan.',
                'data' => $data,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $data = PusatKeSupplier::with([
                'supplier',
                'pusat',
                'barang',
                'kurir',
                'satuanBerat',
                'status'
            ])->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => "Detail Pusat ke Supplier ID: {$id}",
                'data' => $data
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pusat ke Supplier dengan ID {$id} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil detail data.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $data = PusatKeSupplier::findOrFail($id);

            $validated = $request->validate([
                'kode'=> 'required|string|max:255',
                'id_supplier' => 'required|exists:gudang_dan_tokos,id',
                'id_pusat' => 'required|exists:gudang_dan_tokos,id',
                'id_barang' => 'required|exists:barangs,id',
                'id_satuan_berat' => 'required|exists:satuan_berats,id',
                'id_kurir' => 'required|exists:kurirs,id',
                'id_status' => 'required|exists:statuses,id',
                'berat_satuan_barang' => 'required|numeric|min:1',
                'jumlah_barang' => 'required|integer|min:1',
                'tanggal' => 'required|date',
            ]);

            $data->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Data Pusat ke Supplier berhasil diperbarui.',
                'data' => $data,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pusat ke Supplier dengan ID {$id} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $data = PusatKeSupplier::findOrFail($id);
            $data->delete();

            return response()->json([
                'status' => true,
                'message' => "Data Pusat ke Supplier ID {$id} berhasil dihapus.",
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pusat ke Supplier dengan ID {$id} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menghapus data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
