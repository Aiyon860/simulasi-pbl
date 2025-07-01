<?php
namespace App\Http\Controllers;

use Exception;
use App\Models\TrackLog;
use App\Http\Resources\TrackLogShowResource;
use App\Http\Resources\TrackLogIndexResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TrackLogController extends Controller
{
    public function index()
    {
        $logs = TrackLog::select(
            'id', 'id_user', 
            'ip_address', 'aktivitas', 
            'tanggal_aktivitas'
        )->with('user:id,nama_user')
        ->get();

        $headings = [
            'ID', 'Nama User', 'Aktivitas', 'Tanggal Aktivitas',
        ];

        return response()->json([
            'status' => true,
            'message' => 'Data Track Log Semua Aktivitas Gudang',
            'data' => TrackLogIndexResource::collection($logs),

            /** @var array<int, string> */
            'headings' => $headings,
        ]);
    }

    public function show(string $id)
    {
        try {
            $trackLog = TrackLog::with(
                'user:id,nama_user'
            )->findOrFail($id, [
                'id', 
                'id_user', 
                'ip_address',
                'aktivitas', 
                'tanggal_aktivitas'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Data Track Log Spesifik',
                'data' => new TrackLogShowResource($trackLog),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data track log yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data track log spesifik',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
