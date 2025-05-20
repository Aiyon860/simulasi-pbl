<?php
namespace App\Http\Controllers;
use App\Models\Kurir;
use App\Models\Barang;
use App\Models\Status;
use App\Models\SatuanBerat;
use App\Models\DetailGudang;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use App\Models\PusatKeCabang;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class PusatKeCabangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $pusatKeCabang = PusatKeCabang::with('pusat', 'cabang', 'barang','kurir', 'satuanBerat', 'status')->get();

            return response()->json([
                'status'=> true,
                'message'=> 'Data Penerimaan Di Cabang',
                'data'=> $pusatKeCabang,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'=> false,
                'message'=> 'Terjadi kesalahan saat mengambil data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $barangs = Barang::all();
            $pusat = GudangDanToko::all();
            $cabang = $pusat;
            $status = Status::all();
            $kurir = Kurir::all();
            $satuanBerat = SatuanBerat::all();

            return response()->json([
                'status' => true,
                'message' => 'Data Barang, Jenis Penerimaan, dan Asal Barang',
                'data' => [
                    'barangs' => $barangs,
                    'cabang' =>$cabang,
                    'satuanBerat' => $satuanBerat,
                    'status'=>$status,
                    'kurir' => $kurir,
                    'asalBarang'=>$pusat,
                ]    
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'=> false,
                'message'=> 'Terjadi kesalahan saat mengambil data form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode' => 'required|string',
                'id_pusat' => 'required|exists:gudang_dan_tokos,id',
                'id_cabang' => 'required|exists:gudang_dan_tokos,id',
                'id_barang' => 'required|exists:barangs,id',
                'jumlah_barang' => 'required|integer|min:1',
                'tanggal' => 'required|date',
                'id_satuan_berat' => 'required|exists:satuan_berats,id',
                'id_kurir' => 'required|exists:kurirs,id',
                'id_status' => 'required|exists:statuses,id',
                'berat_satuan_barang' => 'required|numeric|min:1',
            ]);

            return DB::transaction(function () use ($validated, $request) {
                $barang = DetailGudang::where('id_gudang', $request->id_pusat)
                    ->where('id_barang', $request->id_barang)
                    ->firstOrFail(['jumlah_stok']);

                if ($barang->jumlah_stok < $request->jumlah_barang) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Jumlah stok tidak mencukupi untuk dikirim.',
                    ]);
                }

                PusatKeCabang::create($validated); 
        
                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil mengirimkan barang dari Pusat Ke Cabang.',
                ]);
            }, 3);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Stok barang tidak ditemukan.',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengirimkan barang dari Pusat Ke Cabang.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $pusatKeCabang = PusatKeCabang::with('pusat', 'cabang', 'barang', 'kurir', 'satuanBerat', 'status')->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => "Data Pusat Ke Cabang dengan ID: {$id}",
                'data' => $pusatKeCabang,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pusat Ke Cabang dengan ID: {$id} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data dengan ID: {$id}",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $pusatKeCabang = PusatKeCabang::findOrFail($id);

            if ($pusatKeCabang->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Penerimaan Di Cabang dengan ID: {$id} sudah dihapus sebelumnya.",
                ]);
            }

            return DB::transaction(function () use ($id, $pusatKeCabang) {
                $pusatKeCabang->update(['flag' => 0]);

                return response()->json([
                    'status' => true,
                    'message' => "Berhasil menghapus Data Penerimaan Di Cabang dengan ID: {$id}",
                ]);
            }, 3);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pusat Ke Cabang dengan ID: {$id} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menghapus Data Penerimaan Di Cabang dengan ID: {$id}",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }
}
