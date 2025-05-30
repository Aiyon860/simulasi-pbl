<?php

namespace App\Http\Controllers;

use App\Models\GudangDanToko;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $users = User::with('role:id,nama_role')
                ->orderBy('id')
                ->get([
                    'id', 'nama_user', 'email', 'id_role', 'id_lokasi', 'flag'
                ]);

            $headings = $users->isEmpty() ? [] : array_keys($users->first()->getAttributes());
            $headings = array_map(function ($heading) {
                return str_replace('_', ' ', ucfirst($heading));
            }, $headings);

            return response()->json([
                'status' => true,
                'message' => 'Data Pengguna',
                'data' => [
                    'users' => $users,
                    'headings' => $headings,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data pengguna.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function create()
    {
        try {
            $roles = Role::select(['id', 'nama_role'])->get();
            $lokasi = GudangDanToko::select(['id', 'nama_gudang_toko'])->get();

            return response()->json([
                'status' => true,
                'message' => 'Data untuk Form Tambah Pengguna',
                'data' => [
                    'roles' => $roles,
                    'lokasi' => $lokasi,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data untuk form tambah pengguna.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_user' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'id_role' => 'required|exists:roles,id',
                'id_lokasi' => 'required|exists:gudang_dan_tokos,id',
            ]);

            return DB::transaction(function () use ($validated) {
                $validated['password'] = Hash::make($validated['password']);
    
                $user = User::create($validated);
    
                return response()->json([
                    'status' => true,
                    'message' => "Pengguna {$user->nama_user} berhasil ditambahkan!",
                    'data' => $user,
                ], 201);
            }, 3);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menyimpan pengguna.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $user = User::with([
                'role:id,nama_role', 
                'lokasi:id,nama_gudang_toko'
            ])->findOrFail($id, [
                'id', 'nama_user', 'email', 'id_role', 'id_lokasi', 'flag'
            ]);

            return response()->json([
                'status' => true,
                'message' => "Detail Data Pengguna dengan ID: {$id}",
                'data' => $user,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pengguna dengan ID: {$id} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil detail data pengguna dengan ID: {$id}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(string $id)
    {
        try {
            $user = User::with([
                'role:id,nama_role', 'lokasi:id,nama_gudang_toko'
            ])->findOrFail($id, [
                'id', 'nama_user', 'email', 'id_role', 'id_lokasi', 'flag'
            ]);
            $roles = Role::select(['id', 'nama_role'])->get();
            $lokasis = GudangDanToko::select(['id', 'nama_gudang_toko'])->get();

            return response()->json([
                'status' => true,
                'message' => 'Data untuk Form Edit Pengguna',
                'data' => [
                    'user' => $user,
                    'roles' => $roles,
                    'lokasis' => $lokasis,
                ],
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pengguna dengan ID: {$id} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data untuk form edit pengguna.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
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
                'message' => "Data Pengguna dengan ID: {$id} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data pengguna.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deactivate(string $id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->id === Auth::id()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Anda tidak dapat menonaktifkan akun yang sedang digunakan.',
                ], 403); // Forbidden
            }

            if ($user->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Pengguna dengan ID: {$id} sudah dinonaktifkan sebelumnya.",
                ]);
            }

            return DB::transaction(function () use ($user) {
                // Soft delete by setting flag to 0
                $user->update(['flag' => 0]);

                return response()->json([
                    'status' => true,
                    'message' => "Pengguna {$user->nama_user} berhasil dinonaktifkan!",
                    'data' => $user,
                ]);
            }, 3);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pengguna dengan ID: {$id} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat menonaktifkan pengguna dengan ID {$id}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function activate(string $id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->flag == 1) {
                return response()->json([
                    'status' => false,
                    'message' => "Pengguna {$user->nama_user} sudah diaktifkan sebelumnya.",
                ]);
            }

            return DB::transaction(function () use ($user) {
                // Set flag to 1 to activate the user
                $user->update(['flag' => 1]);

                return response()->json([
                    'status' => true,
                    'message' => "Pengguna {$user->nama_user} berhasil diaktifkan!",
                    'data' => $user,
                ]);
            }, 3);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pengguna dengan ID: {$id} tidak ditemukan.",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengaktifkan pengguna dengan ID {$id}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}