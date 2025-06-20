<?php
namespace App\Http\Controllers;

use App\Enums\TrackLogEnum;
use Exception;
use App\Models\TrackLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\TrackLogIndexResource;

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
            'ID', 'Nama User', 'IP Address', 'Aktivitas', 'Tanggal Aktivitas',
        ];

        return response()->json([
            'status' => true,
            'message' => 'Data Track Log Semua Aktivitas Gudang',
            'data' => TrackLogIndexResource::collection($logs),

            /** @var array<int, string> */
            'headings' => $headings,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_user' => 'required|exists:users,id',
                'ip_address' => 'required|ip|max:45',
                'aktivitas' => 'required|string',
                'tanggal_aktivitas' => 'required|date',
            ]);

            DB::transaction(function () use ($validated) {
                TrackLog::create($validated);
            }, 3);

            return response()->json([
                'status' => true,
                'message' => "Track log aktivitas {$request->aktivitas} berhasil dibuat!",
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat track log',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // public function show(TrackLog $trackLog)
    // {
    //     return response()->json([
    //         'success' => true,
    //         'data' => $trackLog->load('user'),
    //     ]);
    // }
}
