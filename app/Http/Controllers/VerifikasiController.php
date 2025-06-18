<?php
namespace App\Http\Controllers;

use App\Models\Verifikasi;
use Illuminate\Http\Request;

class VerifikasiController extends Controller
{
    public function index()
    {
        $data = Verifikasi::all();
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_verifikasi' => 'required|in:belum,sudah,tidak',
        ]);

        $verifikasi = Verifikasi::create([
            'jenis_verifikasi' => $request->jenis_verifikasi
        ]);

        return response()->json($verifikasi, 201);
    }

    public function show($id)
    {
        $verifikasi = Verifikasi::findOrFail($id);
        return response()->json($verifikasi);
    }

    public function update(Request $request, $id)
    {
        $verifikasi = Verifikasi::findOrFail($id);

        $request->validate([
            'jenis_verifikasi' => 'required|in:belum,sudah,tidak',
        ]);

        $verifikasi->update([
            'jenis_verifikasi' => $request->jenis_verifikasi
        ]);

        return response()->json($verifikasi);
    }

    public function destroy($id)
    {
        $verifikasi = Verifikasi::findOrFail($id);
        $verifikasi->delete();

        return response()->json(['message' => 'Verifikasi deleted']);
    }
}
