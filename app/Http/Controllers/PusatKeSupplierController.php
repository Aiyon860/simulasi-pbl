<?php

namespace App\Http\Controllers;

use App\Models\Kurir;
use App\Models\Barang;
use App\Models\Status;
use App\Models\SatuanBerat;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use App\Models\PusatKeSupplier;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\StatusResource;
use App\Http\Resources\KurirCreateResource;
use App\Http\Resources\BarangCreateResource;
use App\Helpers\ShippingAndReturnCodeHelpers;
use App\Http\Resources\SupplierCreateResource;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\SatuanBeratCreateResource;
use App\Http\Resources\PusatKeSupplierShowResource;
use App\Http\Resources\PusatKeSupplierIndexResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PusatKeSupplierController extends Controller
{
    public function index(Request $request)
    {
        try {
            $pusatKeSuppliers = PusatKeSupplier::select([
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

            $headings = [
                'ID',
                'Nama Barang',
                'Tujuan',
                'Jumlah Barang',
                'Tanggal',
                'Status',
            ];

            return response()->json([
                'status' => true,
                'message' => 'Data Pusat Ke Supplier',
                'data' => [
                    'pusatKeSuppliers' => PusatKeSupplierIndexResource::collection($pusatKeSuppliers),
                    'statuses' => StatusResource::collection($statuses),
                    'status_opname' => $opname,
                    
                    /** @var array<int, string> */
                    'headings' => $headings,
                ]
            ]);
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
                'message' => 'Data Barang, Jenis Penerimaan tertentu, Supplier, Pusat, dan informasi pendukung lainnya',
                'data' => [
                    'barangs' => BarangCreateResource::collection($barangs),
                    'supplier' => SupplierCreateResource::collection($supplier),
                    'satuanBerat' => SatuanBeratcreateResource::collection($satuanBerat),
                    'kurir' => KurirCreateResource::collection($kurir),
                ]
            ]);
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
                'id_supplier' => 'required|exists:gudang_dan_tokos,id',
                'id_barang' => 'required|exists:barangs,id',
                'id_satuan_berat' => 'required|exists:satuan_berats,id',
                'id_kurir' => 'required|exists:kurirs,id',
                'berat_satuan_barang' => 'required|numeric|min:1',
                'jumlah_barang' => 'required|integer|min:1',
            ]);

            $currentTime = now();

            $pusatKeSupplier = array_merge($validated, [
                'kode' => ShippingAndReturnCodeHelpers::generatePusatKeSupplierCode($currentTime),
                'id_pusat' => 1,
                'id_status' => 1,
                'tanggal' => $currentTime,
            ]);

            DB::transaction(function () use ($pusatKeSupplier) {
                PusatKeSupplier::create($pusatKeSupplier);
            }, 3);

            return response()->json([
                'status' => true,
                'message' => 'Data Pusat ke Supplier berhasil disimpan.',
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->getMessage(),
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
                'message' => "Detail Pusat ke Supplier dengan Kode: {$data->kode}",
                'data' => new PusatKeSupplierShowResource($data)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pusat ke Supplier yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil detail data Pusat Ke Supplier.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'id_status' => 'required|exists:statuses,id',
            ]);

            $pusatKeSupplier = PusatKeSupplier::with([
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

            if ($pusatKeSupplier->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Pengiriman dari Pusat Ke Supplier dengan Kode: {$pusatKeSupplier->kode} sudah dihapus sebelumnya.",
                ], 409); // Conflict
            }

            $pusatKeSupplier->update($validated);

            return response()->json([
                'status' => true,
                'message' => "Berhasil memperbarui status pengiriman dari Pusat Ke Supplier dengan Kode: {$pusatKeSupplier->kode}.",
                'data' => new PusatKeSupplierIndexResource($pusatKeSupplier),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pengiriman dari Pusat Ke Supplier yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
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
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat memperbarui status pengiriman dari Pusat Ke Supplier.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $pusatKeSupplier = PusatKeSupplier::findOrFail($id, 
            [
                'id', 'kode', 'id_barang',
                'id_pusat', 'id_supplier', 
                'id_satuan_berat', 'berat_satuan_barang', 
                'jumlah_barang', 'tanggal',
                'id_kurir', 'id_status',
                'flag'
            ]);

            if ($pusatKeSupplier->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Pusat ke Supplier Kode {$pusatKeSupplier->kode} sudah dihapus sebelumnya.",
                ], 409);
            }

            DB::transaction(function () use ($pusatKeSupplier) {
                $pusatKeSupplier->update(['flag' => 0]);
            }, 3);

            return response()->json([
                    'status' => true,
                    'message' => "Data Pusat ke Supplier Kode {$pusatKeSupplier->kode} berhasil dihapus.",
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pusat ke Supplier yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
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
