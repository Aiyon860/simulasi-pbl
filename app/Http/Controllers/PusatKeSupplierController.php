<?php

namespace App\Http\Controllers;

use App\Helpers\ShippingAndReturnCodeHelpers;
use App\Models\Kurir;
use App\Models\Barang;
use App\Models\SatuanBerat;
use App\Models\DetailGudang;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use App\Models\PusatKeSupplier;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\KurirCreateResource;
use App\Http\Resources\BarangCreateResource;
use App\Http\Resources\SupplierCreateResource;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\SatuanBeratCreateResource;
use App\Http\Resources\PusatKeSupplierIndexResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PusatKeSupplierController extends Controller
{
    public function index()
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

            $headings = $pusatKeSuppliers->isEmpty() ? [] : array_keys($pusatKeSuppliers->first()->getAttributes());
            $headings = array_map(function ($heading) {
                return str_replace('_', ' ', ucfirst($heading));
            }, $headings);

            return response()->json([
                'status' => true,
                'message' => 'Data Pusat Ke Supplier',
                'data' => [
                    'pusatKeSuppliers' => PusatKeSupplierIndexResource::collection($pusatKeSuppliers),
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
            $barangs = Barang::select(['id', 'nama_barang'])->get();
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

            $barang = DetailGudang::where('id_gudang', 1)   // gudang pusat
                ->where('id_barang', $request->id_barang)
                ->firstOrFail(['jumlah_stok']);

            if ($barang->jumlah_stok < $request->jumlah_barang) {
                return response()->json([
                    'status' => false,
                    'message' => 'Jumlah stok tidak mencukupi untuk dikirim.',
                ], 400);
            }

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
                'message' => 'Berhasil mengirimkan barang dari Pusat Ke Cabang.',
                'data' => $pusatKeSupplier,
            ]);
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
                'message' => "Detail Pusat ke Supplier ID: {$id}",
                'data' => new PusatKeSupplierIndexResource($data)
            ]);
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
            $pusatKeSupplier = PusatKeSupplier::findOrFail($id);

            $validated = $request->validate([
                'id_status' => 'required|exists:statuses,id',
            ]);

            DB::transaction(function () use ($validated, $pusatKeSupplier) {
                $pusatKeSupplier->update($validated);
            });

            return response()->json([
                'status' => true,
                'message' => "Data Pusat ke Supplier dengan ID: {$id} berhasil diperbarui.",
                'data' => new PusatKeSupplierIndexResource($pusatKeSupplier),
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
            $pusatKeSupplier = PusatKeSupplier::findOrFail($id);

            if ($pusatKeSupplier->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Pusat ke Supplier ID {$id} sudah dihapus sebelumnya.",
                ], 409);
            }

            DB::transaction(function () use ($pusatKeSupplier) {
                $pusatKeSupplier->update(['flag' => 0]);
            }, 3);

            return response()->json([
                    'status' => true,
                    'message' => "Data Pusat ke Supplier ID {$id} berhasil dihapus.",
            ]);
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
