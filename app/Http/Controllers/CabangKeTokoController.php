<?php
namespace App\Http\Controllers;

use App\Models\Kurir;
use App\Models\Barang;
use App\Models\Status;
use App\Models\SatuanBerat;
use App\Models\CabangKeToko;
use App\Models\DetailGudang;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\StatusResource;
use App\Http\Resources\TokoCreateResource;
use App\Http\Resources\KurirCreateResource;
use App\Http\Resources\BarangCreateResource;
use App\Http\Resources\CabangCreateResource;
use App\Helpers\CodeHelpers;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\CabangKeTokoShowResource;
use App\Http\Resources\CabangKeTokoIndexResource;
use App\Http\Resources\SatuanBeratCreateResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CabangKeTokoController extends Controller
{
    public function index(Request $request)
    {
        try {
            $cabangKeToko = CabangKeToko::select([
                'id', 'kode', 'id_cabang',
                'id_toko', 'id_barang', 'id_satuan_berat',
                'id_kurir', 'id_status', 'berat_satuan_barang',
                'jumlah_barang', 'tanggal', 'id_verifikasi'
            ])->with([
                'cabang:id,nama_gudang_toko,alamat,no_telepon',
                'toko:id,nama_gudang_toko,alamat,no_telepon',
                'barang:id,nama_barang',
                'kurir:id,nama_kurir',
                'satuanBerat:id,nama_satuan_berat',
                'status:id,nama_status',
                'verifikasi:id,jenis_verifikasi'
            ])
            ->where('flag', 1)
            ->orderBy('tanggal', 'desc')
            ->get();

            $statuses = Status::select(['id', 'nama_status'])->get();
            $opname = $request->attributes->get('opname_status');

            $headings = [
                'NO',
                'Nama Barang',
                'Tujuan',
                'Jumlah Barang',
                'Tanggal',
                'Status',
                'Verifikasi',
            ];

            return response()->json([
                'status' => true,
                'message' => 'Data Cabang Ke Toko',
                'data' => [
                    'cabangKeTokos' => CabangKeTokoIndexResource::collection($cabangKeToko),
                    'statuses' => StatusResource::collection($statuses),
                    'status_opname' => $opname,                    

                    /** @var array<int, string> */
                    'headings' => $headings,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Cabang Ke Toko.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function create()
    {
        try {
            $barang = Barang::select(['id', 'nama_barang'])
                ->where('flag', 1)
                ->get();
            $satuanBerat = SatuanBerat::select(['id', 'nama_satuan_berat'])->get();
            $kurir = Kurir::select(['id', 'nama_kurir'])->get();

            // Query builder menjadi immutable, maka harus mengclone base query builder nya
            $gudangDanToko = GudangDanToko::select(['id', 'nama_gudang_toko', 'kategori_bangunan'])
                ->where('id', '!=', 1)
                ->where('kategori_bangunan', '!=', '1')
                ->where('flag', 1);
            $cabang = (clone $gudangDanToko)->where('kategori_bangunan', '=', 0)->get();
            $toko = (clone $gudangDanToko)->where('kategori_bangunan', '=', 2)->get();

            return response()->json([
                'status' => true,
                'message' => 'Data Barang Cabang ke Toko',
                'data' => [
                    'barang' => BarangCreateResource::collection($barang),
                    'satuanBerat' => SatuanBeratCreateResource::collection($satuanBerat),
                    'kurir' => KurirCreateResource::collection($kurir),
                    'cabang' => CabangCreateResource::collection($cabang),
                    'toko' => TokoCreateResource::collection($toko),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data untuk form Cabang ke Toko.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_cabang' => 'required|exists:gudang_dan_tokos,id',
                'id_toko' => 'required|exists:gudang_dan_tokos,id',
                'id_barang' => 'required|exists:barangs,id',
                'id_kurir' => 'required|exists:kurirs,id',
                'jumlah_barang' => 'required|integer|min:1',
            ]);

            $barang = DetailGudang::where('id_gudang', $request->id_cabang)
                ->where('id_barang', $request->id_barang)
                ->first();

            if (!$barang || $barang->jumlah_stok < $request->jumlah_barang) {
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

            $cabangKeToko = array_merge($validated, [
                'kode' => CodeHelpers::generateCabangKeTokoCode($currentTime),
                'id_status' => 1,
                'id_satuan_berat' => $barangGeneral->id_satuan_berat,
                'berat_satuan_barang' => $barangGeneral->berat_satuan_barang,
                'tanggal' => $currentTime,
            ]);

            DB::transaction(function () use ($cabangKeToko) {
                CabangKeToko::create($cabangKeToko);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => 'Barang berhasil terkirim ke Toko',
            ], 201); // Created
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'error' => $e->errors()
            ], 422); // Unprocessable Entity
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengirimkan barang. Silakan coba lagi.',
                'error' => $th->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    public function show(string $id)
    {
        try {
            $CabangKeToko = CabangKeToko::with([
                'cabang:id,nama_gudang_toko,alamat,no_telepon',
                'toko:id,nama_gudang_toko,alamat,no_telepon',
                'barang:id,nama_barang',
                'kurir:id,nama_kurir',
                'satuanBerat:id,nama_satuan_berat',
                'status:id,nama_status',
                'verifikasi:id,jenis_verifikasi'
            ])->findOrFail($id, [
                'id', 'kode', 'id_cabang',
                'id_toko', 'id_barang', 'id_satuan_berat',
                'id_kurir', 'id_status', 'berat_satuan_barang',
                'jumlah_barang', 'tanggal', 'id_verifikasi'
            ]);

            // Gunakan 'kode' atau atribut lain yang lebih deskriptif
            $identifier = $CabangKeToko->kode ?? $id; // Fallback ke ID jika kode tidak ada

            return response()->json([
                'status' => true,
                'message' => "Data Cabang Ke Toko dengan kode: {$identifier} berhasil ditemukan.",
                'data' => new CabangKeTokoShowResource($CabangKeToko),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Cabang Ke Toko tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404); // Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data Cabang Ke Toko.",
                'error' => $th->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'id_status' => 'nullable|exists:statuses,id',
                'id_verifikasi' => 'nullable|exists:verifikasi,id',
            ]);

            $cabangKeToko = CabangKeToko::findOrFail($id);

            $identifier = $cabangKeToko->kode ?? null; // Coba ambil kode jika ada
            if (!$identifier && $cabangKeToko->relationLoaded('barang') && $cabangKeToko->relationLoaded('toko')) {
                $identifier = "pengiriman barang '{$cabangKeToko->barang->nama_barang}' dari cabang '{$cabangKeToko->cabang->nama_gudang_toko}' ke toko '{$cabangKeToko->toko->nama_gudang_toko}'";
            } elseif (!$identifier) {
                $identifier = "transaksi ini"; // Fallback jika tidak ada info spesifik
            }

            if ($cabangKeToko->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Pengiriman dari Cabang Ke Toko dengan Kode: {$identifier} sudah dihapus sebelumnya.",
                ], 409); // Conflict
            }

            $pesan = null;
            if (isset($validated['id_verifikasi'])) {
                $pesan = "Pengiriman ke toko dengan kode: {$cabangKeToko->kode} berhasil diverifikasi.";
            } else if (isset($validated['id_status'])) {
                $namaCabang = $cabangKeToko->cabang->nama_gudang_toko;
                $namaStatusBaru = Status::find($validated['id_status'])->nama_status;
                $pesan = "Status pengiriman ke toko '{$namaCabang}' telah diperbarui menjadi '{$namaStatusBaru}'";
            }

            DB::transaction(function () use ($cabangKeToko, $validated) {
                $cabangKeToko->update($validated);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock


            return response()->json([
                'status' => true,
                'message' => "Berhasil memperbarui status pengiriman dari Cabang Ke Toko dengan kode: {$identifier}",
                'data' => new CabangKeTokoIndexResource($cabangKeToko),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data pengiriman yang Anda cari tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid. Mohon periksa kembali input Anda.',
                'error' => $e->errors()
            ], 422); // Unprocessable Entity
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat memperbarui status pengiriman. Silakan coba lagi.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $CabangKeToko = CabangKeToko::findOrFail($id);

            // Asumsi model CabangKeToko memiliki kolom 'kode' atau relasi untuk 'barang' dan 'toko'
            $identifier = $CabangKeToko->kode ?? null; // Coba ambil kode jika ada

            // Memuat relasi yang diperlukan jika belum dimuat, untuk pembentukan identifier yang lebih baik
            if (!$identifier) {
                $CabangKeToko->loadMissing(['barang:id,nama_barang', 'cabang:id,nama_gudang_toko', 'toko:id,nama_gudang_toko']);
                if ($CabangKeToko->relationLoaded('barang') && $CabangKeToko->relationLoaded('cabang') && $CabangKeToko->relationLoaded('toko')) {
                    $identifier = "pengiriman barang '{$CabangKeToko->barang->nama_barang}' dari cabang '{$CabangKeToko->cabang->nama_gudang_toko}' ke toko '{$CabangKeToko->toko->nama_gudang_toko}'";
                }
            }

            if (!$identifier) {
                $identifier = "data pengiriman ini"; // Fallback jika tidak ada info spesifik
            }

            if ($CabangKeToko->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "{$identifier} sudah tidak aktif atau dibatalkan sebelumnya.",
                ], 400); // Bad Request
            }

            DB::transaction(function () use ($CabangKeToko) {
                $CabangKeToko->update(['flag' => 0]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => "{$identifier} berhasil dibatalkan.",
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data pengiriman yang Anda coba hapus tidak ditemukan.",
            ], 404); // Not Found
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat membatalkan data pengiriman. Silakan coba lagi nanti.",
                'error' => $th->getMessage(),
            ], 500); // Internal Server Error
        }
    }
}
