<?php

namespace App\Http\Controllers;

use App\Models\Kurir;
use App\Models\Barang;
use App\Models\Status;
use App\Models\SatuanBerat;
use App\Helpers\CodeHelpers;
use App\Models\DetailGudang;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use App\Models\SupplierKePusat;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\StatusResource;
use App\Http\Resources\KurirCreateResource;
use App\Http\Resources\BarangCreateResource;
use App\Http\Resources\SupplierCreateResource;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\SatuanBeratCreateResource;
use App\Http\Resources\SupplierKePusatIndexResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SupplierKePusatController extends Controller
{
    public function index(Request $request)
    {
        try {
            $SupplierKePusats = SupplierKePusat::select([
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

            $statuses = Status::select(['id', 'nama_status'])->get();
            $opname = $request->attributes->get('opname_status');

            $headings = $SupplierKePusats->isEmpty() ? [] : array_keys($SupplierKePusats->first()->getAttributes());
            $headings = array_map(function ($heading) {
                return str_replace('_', ' ', ucfirst($heading));
            }, $headings);

            return response()->json([
                'status' => true,
                'message' => 'Data Supllier Ke Pusat',
                'data' => [
                    'SupplierKePusats' => SupplierKePusatIndexResource::collection($SupplierKePusats),
                    'statuses' => StatusResource::collection($statuses),
                    'status_opname' => $opname,

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
            $barangs = Barang::select(['id', 'nama_barang'])
                ->where('flag', '=', 1)
                ->get();
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
                'id_supplier' => 'required|exists:gudang_dan_tokos,id',
                'id_barang' => 'required|exists:barangs,id',
                'jumlah_barang' => 'required|integer|min:1',
                'id_satuan_berat' => 'required|exists:satuan_berats,id',
                'id_kurir' => 'required|exists:kurirs,id',
                'berat_satuan_barang' => 'required|numeric|min:0',
            ]);

            $barang = DetailGudang::where('id_gudang', $request->id_supplier)   // gudang pusat
                ->where('id_barang', $request->id_barang)
                ->firstOrFail(['jumlah_stok']);

            if ($barang->jumlah_stok < $request->jumlah_barang) {
                $namaBarang = $barang?->barang?->nama_barang ?? 'Barang tidak ditemukan';
                $stokTersedia = $barang?->jumlah_stok ?? 0;
                return response()->json([
                    'status' => false,
                    'message' => "Stok untuk barang \"$namaBarang\" tidak mencukupi. Diminta: {$request->jumlah_barang}, Tersedia: $stokTersedia.",
                ], 409);
            }

            $currentTime = now();

            $supplierKePusat = array_merge($validated, [
                'kode' => CodeHelpers::generateSupplierKePusatCode($currentTime),
                'id_status' => 1,
                'id_pusat' => 1,
                'tanggal' => $currentTime,
            ]);

            DB::transaction(function () use ($supplierKePusat) {
                SupplierKePusat::create($supplierKePusat);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => 'Pengiriman barang berhasil dikirimkan dari Supplier Ke Pusat',    
            ]);
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
            $SupplierKePusats = SupplierKePusat::with([
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
                'message' => "Data Supplier Ke Pusat dengan kode: {$SupplierKePusats->kode}",
                'data' => new SupplierKePusatIndexResource($SupplierKePusats),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Supplier Ke Pusat yang dicari tidak ditemukan.",
                'error' => $e->getMessage()
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data Supplier Ke Pusat.",
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $supplierKePusats = SupplierKePusat::with([
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
                'id_kurir', 'id_status', 'flag'
            ]);

            $validated = $request->validate([
                'id_status' => 'required|exists:statuses,id',
            ]);

            DB::transaction(function () use ($validated, $supplierKePusats) {
                $supplierKePusats->update($validated);
            });
            
            return response()->json([
                'status' => true,
                'message' => "Data Pusat ke Supplier dengan kode: {$supplierKePusats->kode} berhasil diperbarui.",
                'data' => new SupplierKePusatIndexResource($supplierKePusats),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->getMessage(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pusat ke Supplier yang dicari tidak ditemukan.",
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data Pusat Ke Supplier.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
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
                'id_kurir', 'id_status', 'flag'
            ]);

            if ($SupplierKePusat->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Supplier Ke Pusat dengan kode: {$SupplierKePusat->kode} sudah dihapus sebelumnya",
                ]);
            }

            DB::transaction(function () use ($SupplierKePusat) {
                $SupplierKePusat->update(['flag' => 0]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock
            
            return response()->json([
                'status' => true,
                'message' => "Berhasil menghapus Data Supplier Ke Pusat dengan kode: {$SupplierKePusat->kode}",
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Supplier Ke Pusat yang dicari tidak ditemukan.",
                'error' => $e->getMessage()
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menghapus Data Supplier Ke Pusat dengan.",
                'error' => $th->getMessage(),
            ], 500);
        }
    }
} 