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
use App\Models\PusatKeSupplier;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\StatusResource;
use App\Http\Resources\KurirCreateResource;
use App\Http\Resources\BarangCreateResource;
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
                'id_kurir', 'id_status', 'id_verifikasi',
            ])->with([
                'pusat:id,nama_gudang_toko', 
                'supplier:id,nama_gudang_toko', 
                'barang:id,nama_barang',
                'kurir:id,nama_kurir', 
                'satuanBerat:id,nama_satuan_berat', 
                'status:id,nama_status',
                'verifikasi:id,jenis_verifikasi'
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
                'Verifikasi',
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
                'id_kurir' => 'required|exists:kurirs,id',
                'jumlah_barang' => 'required|integer|min:1',
            ]);

            $barang = DetailGudang::where('id_gudang', 1)   // gudang pusat
                ->where('id_barang', $request->id_barang)
                ->firstOrFail(['jumlah_stok']);

            if ($barang->jumlah_stok < $request->jumlah_barang) {
                $namaBarang = $barang?->barang?->nama_barang ?? 'Barang tidak ditemukan';
                $stokTersedia = $barang?->jumlah_stok ?? 0;
                return response()->json([
                    'status' => false,
                    'message' => "Stok untuk barang {$namaBarang} tidak mencukupi. Diminta: {$request->jumlah_barang}, Tersedia: $stokTersedia.",
                ], 409);
            }

            $barangGeneral = Barang::findOrFail($request->id_barang, [
                'id', 'id_satuan_berat', 'berat_satuan_barang'
            ]);

            $currentTime = now();

            $pusatKeSupplier = array_merge($validated, [
                'kode' => CodeHelpers::generatePusatKeSupplierCode($currentTime),
                'id_pusat' => 1,
                'id_status' => 1,
                'id_satuan_berat' => $barangGeneral->id_satuan_berat,
                'berat_satuan_barang' => $barangGeneral->berat_satuan_barang,
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
                'errors' => $e->errors()
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
                'status:id,nama_status',
                'verifikasi:id,jenis_verifikasi'
            ])->findOrFail($id, [
                'id', 'kode', 'id_barang',
                'id_pusat', 'id_supplier', 
                'id_satuan_berat', 'berat_satuan_barang', 
                'jumlah_barang', 'tanggal',
                'id_kurir', 'id_status', 'id_verifikasi'
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
                'id_status' => 'nullable|exists:statuses,id',
                'id_verifikasi' => 'nullable|exists:verifikasi,id',
            ]);

            $pusatKeSupplier = PusatKeSupplier::with([
                'pusat:id,nama_gudang_toko', 
                'supplier:id,nama_gudang_toko', 
                'barang:id,nama_barang',
                'kurir:id,nama_kurir', 
                'satuanBerat:id,nama_satuan_berat', 
                'status:id,nama_status',
                'verifikasi:id,jenis_verifikasi'
            ])->findOrFail($id, [
                'id', 'kode', 'id_barang',
                'id_pusat', 'id_supplier', 
                'id_satuan_berat', 'berat_satuan_barang', 
                'jumlah_barang', 'tanggal',
                'id_kurir', 'id_status', 'flag',
                'id_verifikasi'
            ]);

            if ($pusatKeSupplier->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Pengiriman dari Pusat Ke Supplier dengan Kode: {$pusatKeSupplier->kode} sudah dihapus sebelumnya.",
                ], 409); // Conflict
            }

            $pesan = null;
            if (isset($validated['id_verifikasi'])) {
                $pesan = "Retur ke supplier dengan kode: {$pusatKeSupplier->kode} berhasil diverifikasi.";
            } else if (isset($validated['id_status'])) {
                $namaSupplier = $pusatKeSupplier->supplier->nama_gudang_toko;
                $namaStatusBaru = Status::find($validated['id_status'])->nama_status;
                $pesan = "Status retur ke supplier '{$namaSupplier}' telah diperbarui menjadi '{$namaStatusBaru}'";
            }

            DB::transaction(function () use ($pusatKeSupplier, $validated) {
                $pusatKeSupplier->update($validated);
            }, 3);


            return response()->json([
                'status' => true,
                'message' => $pesan,
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
                'errors' => $e->errors()
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
