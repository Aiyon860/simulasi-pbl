<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TokoKeCabang;
use Illuminate\Support\Facades\DB;
use App\Models\Barang;
use App\Models\Kurir;
use App\Models\GudangDanToko;
use App\Models\SatuanBerat;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TokoKeCabangController extends Controller
{
    public function index()
    {
        try {
            $TokoKeCabang = TokoKeCabang::select([
                'id', 'kode', 'id_cabang',
                'id_toko', 'id_barang', 'id_satuan_berat',
                'id_kurir', 'id_status', 'berat_satuan_barang',
                'jumlah_barang', 'tanggal'
            ])->with([
                'cabang:id,nama_gudang_toko,alamat,no_telepon',
                'toko:id,nama_gudang_toko,alamat,no_telepon',
                'barang:id,nama_barang',
                'kurir:id,nama_kurir',
                'satuanBerat:id,nama_satuan_berat',
                'status:id,nama_status'
            ])->where('flag', 1)
            ->orderBy('tanggal', 'desc')
            ->get();

            $headings = $TokoKeCabang->isEmpty() ? [] : array_keys($TokoKeCabang->first()->getAttributes());
            $headings = array_map(function ($heading) {
                return str_replace('_', ' ', ucfirst($heading));
            }, $headings);

            return response()->json([
                'status' => true,
                'message' => 'Data Toko Ke Cabang',
                'data' => [
                    'TokoKeCabangs' => $TokoKeCabang,
                    'headings' => $headings,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Toko Ke Cabang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function create()
    {
        try {
            $barang = Barang::select(['id', 'nama_barang'])
                ->where('flag', '=', 1)
                ->get();
            $satuanBerat = SatuanBerat::select(['id', 'nama_satuan_berat'])->get();
            $kurir = Kurir::select(['id', 'nama_kurir'])->get();

            // Query builder menjadi immutable, maka harus mengclone base query builder nya
            $gudangDanToko = GudangDanToko::select(['id', 'nama_gudang_toko', 'kategori_bangunan'])
                ->where('id', '!=', 1)
                ->where('kategori_bangunan', '!=', '1')
                ->where('flag', '=', 1);
            $cabang = (clone $gudangDanToko)->where('kategori_bangunan', '=', 0)->get();
            $toko = (clone $gudangDanToko)->where('kategori_bangunan', '=', 2)->get();

            return response()->json([
                'status' => true,
                'message' => 'Data pendukung untuk form Toko Ke Cabang.',
                'data' => [
                    'barang' => $barang,
                    'satuanBerat' => $satuanBerat,
                    'kurir' => $kurir,
                    'toko' => $toko,
                    'cabang' => $cabang,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyiapkan data untuk form Toko Ke Cabang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode' => 'required|string|unique:toko_ke_cabangs,kode',
                'id_cabang' => 'required|exists:gudang_dan_tokos,id',
                'id_toko' => 'required|exists:gudang_dan_tokos,id',
                'id_barang' => 'required|exists:barangs,id',
                'id_satuan_berat' => 'required|exists:satuan_berats,id',
                'id_kurir' => 'nullable|exists:kurirs,id',
                'id_status' => 'required|exists:statuses,id',
                'berat_satuan_barang' => 'required|numeric|min:0',
                'jumlah_barang' => 'required|integer|min:1',
                'tanggal' => 'required|date',
            ]);

            return DB::transaction(function () use ($validated) {
                $tokoKeCabang = TokoKeCabang::create($validated);

                return response()->json([
                    'status' => true,
                    'message' => 'Retur berhasil dikirim dari Toko ke Cabang.',
                    'data' => $tokoKeCabang,
                ], 201); // 201 Created
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->errors(),
            ], 422); // Unprocessable Entity
        } catch (\Throwable $th) { // Using Throwable for broader error catching
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengirimkan barang Dari Toko ke Cabang. Silakan coba lagi.',
                'error' => $th->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    public function show(string $id)
    {
        try {
            $tokoKeCabang = TokoKeCabang::with([
                'cabang:id,nama_gudang_toko,alamat,no_telepon',
                'toko:id,nama_gudang_toko,alamat,no_telepon',
                'barang:id,nama_barang',
                'kurir:id,nama_kurir',
                'satuanBerat:id,nama_satuan_berat',
                'status:id,nama_status'
            ])->findOrFail($id, [
                'id', 'kode', 'id_cabang',
                'id_toko', 'id_barang', 'id_satuan_berat',
                'id_kurir', 'id_status', 'berat_satuan_barang',
                'jumlah_barang', 'tanggal'
            ]);

            return response()->json([
                'status' => true,
                'message' => "Detail Data Toko Ke Cabang dengan ID: {$id}.",
                'data' => $tokoKeCabang,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Toko Ke Cabang dengan ID: {$id} tidak ditemukan.",
            ], 404); // Not Found
        } catch (\Exception $e) { // Catching a general Exception for other errors
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil detail data Toko Ke Cabang dengan ID: {$id}.",
                'error' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    public function edit(string $id)
    {
        // This method is intentionally left empty as per your request.
    }

    public function update(Request $request, string $id)
    {
        try {
            $CabangKeToko = TokoKeCabang::findOrFail($id);

            $validated = $request->validate([
                'id_status' => 'required|exists:statuses,id',
            ]);

            return DB::transaction(function () use ($validated, $CabangKeToko) {
                $CabangKeToko->update($validated);

                return response()->json([
                    'status' => true,
                    'message' => 'Data Toko ke Cabang berhasil diperbarui',
                    'data' => $CabangKeToko,
                ]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->errors(),
            ], 422); // Unprocessable Entity
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Toko ke Cabang dengan ID: {$id} tidak ditemukan.",
            ], 404); // Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui data Toko ke Cabang. Silakan coba lagi.',
                'error' => $th->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    public function destroy(string $id)
    {
        try {
            $tokoKeCabang = TokoKeCabang::findOrFail($id);

            if ($tokoKeCabang->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Toko Ke Cabang dengan ID: {$id} sudah dihapus.",
                ], 409); // Conflict status code for already deleted
            }

            return DB::transaction(function () use ($tokoKeCabang, $id) {
                $tokoKeCabang->update(['flag' => 0]);

                return response()->json([
                    'status' => true,
                    'message' => "Data Toko Ke Cabang dengan ID: {$id} berhasil dihapus (dinonaktifkan).",
                ]);
            }, 3);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Toko Ke Cabang dengan ID: {$id} tidak ditemukan.",
            ], 404); // Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menghapus Data Toko Ke Cabang dengan ID: {$id}.",
                'error' => $th->getMessage(),
            ], 500); // Internal Server Error
        }
    }
}
