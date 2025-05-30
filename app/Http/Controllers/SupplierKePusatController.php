<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupplierKePusat;
use Illuminate\Support\Facades\DB;
use App\Models\Kurir;
use App\Models\Barang;
use App\Models\SatuanBerat;
use App\Http\Resources\KurirCreateResource;
use App\Http\Resources\SatuanBeratCreateResource;
use App\Http\Resources\BarangCreateResource;
use App\Http\Resources\SupplierCreateResource;
use App\Http\Resources\SupplierKePusatIndexResource;
use App\Models\GudangDanToko;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class SupplierKePusatController extends Controller
{
    public function index()
    {
        try {
            $SupplierKePusat = SupplierKePusat::select([
                'id', 'kode', 'id_barang',
                'id_pusat', 'id_supplier', 
                'id_satuan_berat', 'berat_satuan_barang', 
                'jumlah_barang', 'tanggal',
                'id_kurir', 'id_status',
            ])->with([
                'pusat:id,nama_gudang_toko', 
                'supplier:id,nama_gudang_toko', 
                'barang:id,nama_barang',
                'kurir:id,nama_kurir', 
                'satuanBerat:id,nama_satuan_berat', 
                'status:id,nama_status'
            ])->where('flag', 1)
            ->orderBy('tanggal', 'desc')
            ->get();

            $headings = $SupplierKePusat->isEmpty() ? [] : array_keys($SupplierKePusat->first()->getAttributes());
            $headings = array_map(function ($heading) {
                return str_replace('_', ' ', ucfirst($heading));
            }, $headings);

            return response()->json([
                'status' => true,
                'message' => 'Data Supllier Ke Pusat',
                'data' => [
                    'SupplierKePusats' => SupplierKePusatIndexResource::collection($SupplierKePusats),
                    /** @var array<int, string> */
                    'headings' => $headings,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Supplier Ke Pusat.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function create()
    {
        try {
            $barangs = Barang::select(['id', 'nama_barang'])->get();
            $supplier = GudangDanToko::select(['id', 'nama_gudang_toko'])
                ->where('flag', 1)
                ->where('kategori_bangunan', 1)
                ->get();
            $kurir = Kurir::select(['id', 'nama_kurir'])->get();
            $satuanBerat = SatuanBerat::select(['id', 'nama_satuan_berat'])->get();

            return response()->json([
                'status' => true,
                'message' => 'Data untuk Form Tambah Pengiriman Supplier Ke Pusat',
                'data' => [
                    'barangs' => BarangCreateResource::collection($barangs),
                    'supplier' => SupplierCreateResource::collection($supplier),
                    'satuanBerat' => SatuanBeratCreateResource::collection($satuanBerat),
                    'kurir' => KurirCreateResource::collection($kurir),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data yang dibutuhkan untuk form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode' => 'required|string|max:255',
                'id_supplier' => 'required|exists:gudang_dan_tokos,id',
                'id_barang' => 'required|exists:barangs,id',
                'jumlah_barang' => 'required|integer|min:1',
                'tanggal' => 'required|date',
                'id_satuan_berat' => 'required|exists:satuan_berats,id',
                'id_kurir' => 'required|exists:kurirs,id',
                'berat_satuan_barang' => 'required|numeric|min:0',
            ]);

            DB::transaction(function () use ($validated) {
                $supplierKePusat = SupplierKePusat::create(array_merge($validated, [
                    'id_status' => 1,
                    'id_pusat' => 1,
                ]));

                return response()->json([
                    'status' => true,
                    'message' => 'Pengiriman barang berhasil dikirimkan dari Supplier Ke Pusat',
                    
                ]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Pengiriman barang gagal dikirimkan dari Supplier Ke Pusat.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $SupplierKePusat = SupplierKePusat::with([
                'pusat:id,nama_gudang_toko', 
                'supplier:id,nama_gudang_toko', 
                'barang:id,nama_barang',
                'kurir:id,nama_kurir', 
                'satuanBerat:id,nama_satuan_berat', 
                'status:id,nama_status'
            ])->findOrFail($id, [
                'id', 'kode', 'id_barang',
                'id_pusat', 'id_supplier', 
                'id_satuan_berat', 'berat_satuan_barang', 
                'jumlah_barang', 'tanggal',
                'id_kurir', 'id_status',
            ]);

            return response()->json([
                'status' => true,
                'message' => "Data Supplier Ke Pusat dengan ID: {$id}",
                'data' => SupplierKePusatIndexResource::collection($SupplierKePusats),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Supplier Ke Pusat dengan ID: {$id} tidak ditemukan.",
                'error' => $e->getMessage()
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data Supplier Ke Pusat dengan ID: {$id}.",
                'error' => $th->getMessage(),
            ], 500);
        }
    }


    public function edit(string $id)
    {
        
    }

    public function update(Request $request, string $id)
    {
        try {
            $supplierKePusat = SupplierKePusat::findOrFail($id);

            $validated = $request->validate([
                'id_status' => 'required|exists:statuses,id',
            ]);

            DB::transaction(function () use ($id, $validated, $supplierKePusat) {
                $supplierKePusat->update($validated);
    
                return response()->json([
                    'status' => true,
                    'message' => "Data Pusat ke Supplier dengan ID: {$id} berhasil diperbarui.",
                    'data' => SupplierKePusatIndexResource::collection($SupplierKePusat),
                ]);
            });
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->getMessage(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pusat ke Supplier dengan ID {$id} tidak ditemukan.",
                'error' => $e->getMessage()
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
            $SupplierKePusat = SupplierKePusat::findOrFail($id);

            if ($SupplierKePusat->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Supplier Ke Pusat dengan ID: {$id} sudah dihapus sebelumnya",
                ]);
            }

             DB::transaction(function () use ($id, $SupplierKePusat) {
                $SupplierKePusat->update(['flag' => 0]);

                return response()->json([
                    'status' => true,
                    'message' => "Berhasil menghapus Data Supplier Ke Pusat dengan ID: {$id}",
                ]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Supplier Ke Pusat dengan ID: {$id} tidak ditemukan.",
                'error' => $e->getMessage()
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menghapus Data Supplier Ke Pusat dengan ID: {$id}.",
                'error' => $th->getMessage(),
            ], 500);
        }
    }
} 