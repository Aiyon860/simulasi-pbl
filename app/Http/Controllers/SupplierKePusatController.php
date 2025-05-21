<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupplierKePusat;
use Illuminate\Support\Facades\DB;
use App\Models\Kurir;
use App\Models\Barang;
use App\Models\Status;
use App\Models\SatuanBerat;
use App\Models\GudangDanToko;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class SupplierKePusatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $SupplierKePusat = SupplierKePusat::with('supplier', 'pusat', 'barang', 'kurir', 'satuanBerat', 'status')->get();

            return response()->json([
                'status' => true,
                'message' => 'Data Supllier Ke Pusat',
                'data' => $SupplierKePusat,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Supplier Ke Pusat.',
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
            $supplier = GudangDanToko::all();
            $pusat = $supplier;
            $status = Status::all();
            $kurir = Kurir::all();
            $satuanBerat = SatuanBerat::all();

            return response()->json([
                'status' => true,
                'message' => 'Data untuk Form Tambah Pengiriman Supplier Ke Pusat',
                'data' => [
                    'barangs' => $barangs,
                    'supplier' => $supplier,
                    'satuanBerat' => $satuanBerat,
                    'status' => $status,
                    'kurir' => $kurir,
                    'pusat' => $pusat,
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode' => 'required|string|max:255',
                'id_supplier' => 'required|exists:gudang_dan_tokos,id',
                'id_pusat' => 'required|exists:gudang_dan_tokos,id',
                'id_barang' => 'required|exists:barangs,id',
                'jumlah_barang' => 'required|integer|min:1',
                'tanggal' => 'required|date',
                'id_satuan_berat' => 'required|exists:satuan_berats,id',
                'id_kurir' => 'required|exists:kurirs,id',
                'id_status' => 'required|exists:statuses,id',
                'berat_satuan_barang' => 'required|numeric|min:0',
            ]);

            DB::transaction(function () use ($validated) {
                SupplierKePusat::create($validated);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => 'Pengiriman barang berhasil dikirimkan dari Supplier Ke Pusat',
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Pengiriman barang gagal dikirimkan dari Supplier Ke Pusat.',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $SupplierKePusat = SupplierKePusat::with('supplier', 'pusat', 'barang', 'kurir', 'satuanBerat', 'status')->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => "Data Supplier Ke Pusat dengan ID: {$id}",
                'data' => $SupplierKePusat,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Supplier Ke Pusat dengan ID: {$id} tidak ditemukan.",
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil data Supplier Ke Pusat dengan ID: {$id}.",
                'error' => $th->getMessage(),
            ], 500);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
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

            DB::transaction(function () use ($SupplierKePusat) {
                $SupplierKePusat->update(['flag' => 0]);
            }, 3); // Maksimal 3 percobaan jika terjadi deadlock

            return response()->json([
                'status' => true,
                'message' => "Berhasil menghapus Data Supplier Ke Pusat dengan ID: {$id}",
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Supplier Ke Pusat dengan ID: {$id} tidak ditemukan.",
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