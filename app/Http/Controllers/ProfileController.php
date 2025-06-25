<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserIndexResource;
use App\Http\Resources\RoleCreateResource;
use App\Http\Resources\LokasiCreateResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProfileController extends Controller
{
    public function show(string $id)
    {
        try {
            if (auth()->user()->id != $id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Akses tidak sah',
                ], 403);
            }

            $user = User::with([
                'role:id,nama_role', 
                'lokasi:id,nama_gudang_toko'
            ])->findOrFail($id, [
                'id', 'nama_user', 'email', 'id_role', 'id_lokasi', 'flag'
            ]);

            return response()->json([
                'status' => true,
                'message' => "Detail Data Pengguna dengan {$user->nama_user}",
                'data' => new UserIndexResource($user),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pengguna yang dicari tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil detail data pengguna.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(string $id)
    {
        try {
            if (auth()->user()->id != $id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Akses tidak sah',
                ], 403);
            }

            $user = User::with([
                'role:id,nama_role', 'lokasi:id,nama_gudang_toko'
            ])->findOrFail($id, [
                'id', 'nama_user', 'email', 'id_role', 'id_lokasi', 'flag'
            ]);
            $roles = Role::select(['id', 'nama_role'])->get();
            $lokasis = GudangDanToko::select(['id', 'nama_gudang_toko'])->get();

            return response()->json([
                'status' => true,
                'message' => 'Data user, role, dan lokasi untuk Form Edit Pengguna',
                'data' => [
                    'user' => new UserIndexResource($user),
                    'roles' => RoleCreateResource::collection($roles),
                    'lokasis' => LokasiCreateResource::collection($lokasis),
                ],
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pengguna yang dicari tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data user, role, dan lokasi untuk form edit pengguna.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            if (auth()->user()->id != $id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Akses tidak sah',
                ], 403);
            }

            $user = User::with([
                'role:id,nama_role', 'lokasi:id,nama_gudang_toko'
            ])->findOrFail($id, [
                'id', 'nama_user', 'email', 'id_role', 'id_lokasi', 'flag'
            ]);

            $rules = [
                'nama_user' => 'required|string|max:255',
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'id_role' => 'nullable|exists:roles,id',
                'id_lokasi' => 'nullable|exists:gudang_dan_tokos,id',
            ];

            if ($request->filled('password')) {
                $rules['password'] = 'string|min:8|confirmed';
            }

            $validated = $request->validate($rules);

            if ($request->filled('password')) {
                $validated['password'] = Hash::make($validated['password']);
            }

            DB::transaction(function () use ($user, $validated) {
                $user->update($validated);
            }, 3);

            return response()->json([
                'status' => true,
                'message' => "Data pengguna {$user->nama_user} berhasil diperbarui!",
                'data' => new UserIndexResource($user),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang dibutuhkan untuk mengupdate user tidak valid.',
                'error' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pengguna yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data pengguna.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
