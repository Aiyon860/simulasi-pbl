<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $identifier)
    {
        try {
            if (is_numeric($identifier)) {
                $user = User::with([
                    'role:id,nama_role',
                    'lokasi:id,nama_gudang_toko'
                ])->find($identifier, [
                    'id', 'nama_user', 'email', 'id_role', 'id_lokasi', 'flag', 'created_at', 'updated_at'
                ]);
            } else {
                $user = User::with([
                    'role:id,nama_role',
                    'lokasi:id,nama_gudang_toko'
                ])->where('nama_user', $identifier)
                  ->first([
                    'id', 'nama_user', 'email', 'id_role', 'id_lokasi', 'flag', 'created_at', 'updated_at'
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => "Detail Data Pengguna dengan Nama User: {$identifier}",
                'data' => $user,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pengguna dengan Nama User: {$identifier} tidak ditemukan.",
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil detail data pengguna dengan Nama User: {$identifier}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $identifier)
    {
        try {
            if (is_numeric($identifier)) {
                $user = User::with([
                    'role:id,nama_role',
                    'lokasi:id,nama_gudang_toko'
                ])->find($identifier, [
                    'id', 'nama_user', 'email', 'id_role', 'id_lokasi', 'flag', 'created_at', 'updated_at'
                ]);
            } else {
                $user = User::with([
                    'role:id,nama_role',
                    'lokasi:id,nama_gudang_toko'
                ])->where('nama_user', $identifier)
                  ->first([
                    'id', 'nama_user', 'email', 'id_role', 'id_lokasi', 'flag', 'created_at', 'updated_at'
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Data untuk Form Edit Pengguna',
                'data' => $user,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pengguna dengan Nama User: {$identifier} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data untuk form edit pengguna.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $identifier)
    {
        try {
            if (is_numeric($identifier)) {
                $user = User::with([
                    'role:id,nama_role',
                    'lokasi:id,nama_gudang_toko'
                ])->find($identifier, [
                    'id', 'nama_user', 'email', 'id_role', 'id_lokasi', 'flag'
                ]);
            } else {
                $user = User::with([
                    'role:id,nama_role',
                    'lokasi:id,nama_gudang_toko'
                ])->where('nama_user', $identifier)
                  ->first([
                    'id', 'nama_user', 'email', 'id_role', 'id_lokasi', 'flag'
                ]);
            }

            $rules = [
                'nama_user' => 'required|string|max:255',
            ];

            if ($request->filled('password')) {
                $rules['password'] = 'string|min:8|confirmed';
            }

            $validated = $request->validate($rules);

            return DB::transaction(function () use ($user, $validated, $request) {
                if ($request->filled('password')) {
                    $validated['password'] = Hash::make($validated['password']);
                }

                $user->update($validated);

                return response()->json([
                    'status' => true,
                    'message' => "Data pengguna {$user->nama_user} berhasil diperbarui!",
                    'data' => $user,
                ]);
            }, 3);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pengguna dengan ID: {$identifier} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data pengguna.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
