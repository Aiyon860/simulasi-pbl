<?php
namespace App\Http\Controllers;
use App\Models\Kurir;
use App\Models\Barang;
use App\Models\Status;
use App\Models\SatuanBerat;
use App\Models\DetailGudang;
use Illuminate\Http\Request;
use App\Models\CabangKePusat;
use App\Models\GudangDanToko;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\StatusResource;
use App\Http\Resources\KurirCreateResource;
use App\Http\Resources\BarangCreateResource;
use App\Http\Resources\CabangCreateResource;
use App\Helpers\ShippingAndReturnCodeHelpers;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\CabangKePusatShowResource;
use App\Http\Resources\SatuanBeratCreateResource;
use App\Http\Resources\CabangKePusatIndexResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CabangKePusatController extends Controller
{
    public function index()
    {
        try {
            $CabangKePusat = CabangKePusat::select([
                'id', 'kode', 'id_pusat',
                'id_cabang', 'id_barang', 'id_satuan_berat',
                'id_kurir', 'id_status', 'berat_satuan_barang',
                'jumlah_barang', 'tanggal'
            ])->with(
                'pusat:id,nama_gudang_toko,alamat,no_telepon',
                'cabang:id,nama_gudang_toko,alamat,no_telepon',
                'barang:id,nama_barang',
                'kurir:id,nama_kurir',
                'satuanBerat:id,nama_satuan_berat',
                'status:id,nama_status'
            )->where('flag', 1)
            ->orderBy('tanggal', 'desc')
            ->get();

            $statuses = Status::select(['id', 'nama_status'])->get();

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
                'message' => 'Data Cabang Ke Pusat',
                'data' => [
                    'CabangKePusats' => CabangKePusatIndexResource::collection($CabangKePusat),
                    'statuses' => StatusResource::collection($statuses),

                    /** @var array<int, string> */
                    'headings' => $headings,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Cabang Ke Pusat.',
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
            $kurir = Kurir::select(['id', 'nama_kurir'])->get();
            $cabang = GudangDanToko::select(['id', 'nama_gudang_toko'])
                ->where('id', '!=', 1)
                ->where('kategori_bangunan', '=', 0)
                ->get();
            $satuanBerat = SatuanBerat::select(['id', 'nama_satuan_berat'])->get();

            return response()->json([
                'status' => true,
                'message' => 'Data Barang, Jenis Penerimaan, dan Asal Barang',
                'data' => [
                    'barangs' => BarangCreateResource::collection($barangs),
                    'cabangs' => CabangCreateResource::collection($cabang),
                    'satuanBerat' => SatuanBeratCreateResource::collection($satuanBerat),
                    'kurir' => KurirCreateResource::collection($kurir),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data untuk form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_cabang' => 'required|exists:gudang_dan_tokos,id',
                'id_barang' => 'required|exists:barangs,id',
                'id_satuan_berat' => 'required|exists:satuan_berats,id',
                'id_kurir' => 'required|exists:kurirs,id',
                'berat_satuan_barang' => 'required|numeric|min:1',
                'jumlah_barang' => 'required|integer|min:1',
            ]);

            $barang = DetailGudang::where('id_gudang', $request->id_cabang)
                ->where('id_barang', $request->id_barang)
                ->first('jumlah_stok');
            
            if (!$barang || $barang->jumlah_stok < $request->jumlah_barang) {
            $namaBarang = $barang?->barang?->nama_barang ?? 'Barang tidak ditemukan';
            $stokTersedia = $barang?->jumlah_stok ?? 0;
                return response()->json([ 
                    'status' => false,
                    'message' => "Stok untuk barang \"$namaBarang\" tidak mencukupi. Diminta: {$request->jumlah_barang}, Tersedia: $stokTersedia.",
                ], 409);
            }

            $currentTime = now();

            $cabangKePusat = array_merge($validated, [
                'kode' => ShippingAndReturnCodeHelpers::generateCabangKePusatCode($currentTime),
                'id_pusat' => 1,
                'id_status' => 1,
                'tanggal' => $currentTime,
            ]);

            DB::transaction(function () use ($cabangKePusat) {
                CabangKePusat::create($cabangKePusat);
            }, 3);

            return response()->json([
                'status' => true,
                'message' => 'Barang Berhasil Dikirim Dari Cabang Ke Pusat.',
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan untuk menambah laporan cabang ke pusat tidak valid.',
                'error' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengirimkan barang dari Cabang Ke Pusat.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $cabangKePusat = CabangKePusat::with([
                'pusat:id,nama_gudang_toko,alamat,no_telepon',
                'cabang:id,nama_gudang_toko,alamat,no_telepon',
                'barang:id,nama_barang',
                'kurir:id,nama_kurir',
                'satuanBerat:id,nama_satuan_berat',
                'status:id,nama_status'
            ])->findOrFail($id, [
                'id', 'kode', 'id_pusat',
                'id_cabang', 'id_barang', 'id_satuan_berat',
                'id_kurir', 'id_status', 'berat_satuan_barang',
                'jumlah_barang', 'tanggal'
            ]);

             return response()->json([
            'status' => true,
                'message' => "Detail pengiriman dengan kode: {$cabangKePusat->kode} ({$cabangKePusat->barang->nama_barang}) berhasil ditemukan.",
                'data' => new CabangKePusatIndexResource($cabangKePusat),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Cabang Ke Pusat yang dicari tidak ditemukan:.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data cabang ke pusat",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
        $CabangKeToko = CabangKePusat::with('barang')->findOrFail($id);

        $validated = $request->validate([
            'id_status' => 'required|exists:statuses,id',
        ]);

        DB::transaction(function () use ($validated, $CabangKeToko) {
            $CabangKeToko->update($validated);
        }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => "Status pengiriman dengan kode: {$CabangKeToko->kode} ({$CabangKeToko->barang->nama_barang}) berhasil diperbarui.",
                'data' => new CabangKePusatIndexResource($CabangKeToko),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Cabang Ke Pusat yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan untuk mengupdate laporan cabang ke pusat tidak valid.',
                'error' => $e->getMessage()
            ], 422); // Unprocessable Entity
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat memperbarui status pengiriman dari Cabang Ke Pusat.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
         try {
        $cabangKePusat = CabangKePusat::with('barang')->findOrFail($id);

        if ($cabangKePusat->flag == 0) {
            return response()->json([
                'status' => false,
                'message' => "Data pengiriman dengan kode: {$cabangKePusat->kode} sudah dihapus sebelumnya.",
            ], 409);
        }

        DB::transaction(function () use ($cabangKePusat) {
            $cabangKePusat->update(['flag' => 0]);
        }, 3);

        return response()->json([
            'status' => true,
            'message' => "Berhasil menghapus data pengiriman dengan kode: {$cabangKePusat->kode}.",
        ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Cabang Ke Pusat yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menghapus Data Cabang Ke Pusat.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}