<?php
namespace App\Http\Controllers;

use App\Models\TrackLog;
use App\Models\User;
use Illuminate\Http\Request;

class TrackLogController extends Controller
{
    public function index()
    {
        $logs = TrackLog::with('user')->latest()->get();
        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    public function create()
    {
        $users = User::all();

        return response()->json([
            'success' => true,
            'users' => $users,
            'aktivitas_options' => [
                'pengiriman',
                'penerimaan',
                'retur',
                'perubahan status opname',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'ip_address' => 'required|ip',
            'aktivitas' => 'required|in:pengiriman,penerimaan,retur,perubahan status opname',
            'tanggal_aksi' => 'required|date',
        ]);

        $log = TrackLog::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Log berhasil ditambahkan.',
            'data' => $log,
        ]);
    }

    public function show(TrackLog $trackLog)
    {
        return response()->json([
            'success' => true,
            'data' => $trackLog->load('user'),
        ]);
    }

    public function edit(TrackLog $trackLog)
    {
        $users = User::all();

        return response()->json([
            'success' => true,
            'track_log' => $trackLog,
            'users' => $users,
            'aktivitas_options' => [
                'pengiriman',
                'penerimaan',
                'retur',
                'perubahan status opname',
            ],
        ]);
    }

    public function update(Request $request, TrackLog $trackLog)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'ip_address' => 'required|ip',
            'aktivitas' => 'required|in:pengiriman,penerimaan,retur,perubahan status opname',
            'tanggal_aksi' => 'required|date',
        ]);

        $trackLog->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Log berhasil diperbarui.',
            'data' => $trackLog,
        ]);
    }

    public function destroy(TrackLog $trackLog)
    {
        $trackLog->delete();

        return response()->json([
            'success' => true,
            'message' => 'Log berhasil dihapus.',
        ]);
    }
}
