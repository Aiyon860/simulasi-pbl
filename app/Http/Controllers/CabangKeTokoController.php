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

class CabangKeTokoController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cabangKeToko = CabangKeToko::with('cabang', 'toko', 'barang','kurir','satuanBerat','status')->get();
        return response()->json([
            'status' => true,
            'message' => 'Data Cabang Ke Toko',
            'data' => $cabangKeToko
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $barang = Barang::all();
        $satuanBerat = SatuanBerat::all();
        $kurir = Kurir::all();
        $status = Status::where('id', 1)->get();
        $cabang = GudangDanToko::where('flag', 1)->get();
        $toko = $cabang;
        return response()->json([
            'status' => true,
            'message' => 'Data Barang Cabang ke Toko',
            'data' => [
                'barang' => $barang,
                'satuanBerat' => $satuanBerat,
                'kurir' => $kurir,
                'status' => $status,
                'cabang' => $cabang,
                'toko' => $toko,
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string',
            'id_cabang' => 'required|exists:gudang_dan_tokos,id',
            'id_toko' => 'required|exists:gudang_dan_tokos,id',
            'id_barang' => 'required|exists:barangs,id',
            'id_satuan_berat' => 'required|exists:satuan_berats,id',
            'id_kurir' => 'required|exists:kurirs,id',
            'id_status' => 'required|exists:statuses,id',
            'berat_satuan_barang' => 'required|numeric|min:1',
            'jumlah_barang' => 'required|integer|min:1',
            'tanggal' => 'required|date',
        ]);

        try {
            return DB::transaction(function () use ($validated, $request) {
                $barang = DetailGudang::where('id_gudang', $request->id_cabang)->where('id_barang', $request->id_barang)->first('jumlah_stok');

                if ($barang->jumlah_stok < $request->jumlah_barang) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Jumlah stok tidak mencukupi untuk dikirim.',
                    ]);
                }

                CabangKeToko::create($validated);

                return response()->json([
                    'status' => true,
                    'message' => 'Barang berhasil terkirim ke Toko',
                ]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                // 'message' => 'Gagal mengirimkan barang. Silakan coba lagi.',
                'message' => $th->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $CabangKeToko = CabangKeToko::with('cabang', 'toko', 'barang', 'kurir', 'satuanBerat','status')->findOrFail($id);
            return response()->json([
                'status' => true,
                'message' => "Data Cabang Ke Toko dengan ID: {$id}",
                'data' => $CabangKeToko,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Data Cabang Ke Toko dengan ID: {$id} tidak ditemukan.",
            ]);
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
        // $validated = $request->validate([
        //     'kode' => 'required|string',
        //     'id_cabang' => 'required|exists:gudang_dan_tokos,id',
        //     'id_toko' => 'required|exists:gudang_dan_tokos,id',
        //     'id_barang' => 'required|exists:barangs,id',
        //     'jumlah' => 'required|integer|min:1',
        //     'tanggal' => 'required|date',
        // ]);
        // try {
        //     $CabangKeToko = CabangKeToko::findOrFail($id);
        //     return DB::transaction(function () use ($validated, $CabangKeToko) {

        //         $CabangKeToko->update($validated);

        //         return response()->json([
        //             'status' => true,
        //             'message' => 'Barang berhasil diperbarui',
        //         ]);
        //     }, 3); // Maksimal 3 percobaan jika terjadi deadlock
        // } catch (\Throwable $th) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Gagal memperbarui barang. Silakan coba lagi.',
        //     ]);
        // }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        try {
            $CabangKeToko = CabangKeToko::findOrFail($id);

            if ($CabangKeToko->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Penerimaan Di Toko dengan ID: {$id} sudah dihapus sebelumnya",
                ]);
            }

            return DB::transaction(function () use ($id, $CabangKeToko) {
                $CabangKeToko->update(['flag' => 0]);

                return response()->json([
                    'status' => true,
                    'message' => "Berhasil menghapus Data Penerimaan Di Toko dengan ID: {$id}",
                ]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menghapus Data Penerimaan Di Toko dengan ID: {$id} {th->getMessage()}",
            ]);
        }
    }
}
