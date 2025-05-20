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
    /**
     * Display a listing of the user.
     */
    public function index(Request $request)
    {
        try {
            $users = User::with('role')->orderBy('id')->paginate(10);
            return response()->json([
                'status' => true,
                'message' => 'Data Pengguna',
                'data' => $users,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data pengguna.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        try {
            $roles = Role::all();
            $lokasis = GudangDanToko::all(); // Mengambil semua lokasi (gudang dan toko)
            return response()->json([
                'status' => true,
                'message' => 'Data untuk Form Tambah Pengguna',
                'data' => [
                    'roles' => $roles,
                    'lokasis' => $lokasis,
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

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_user' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'id_role' => 'nullable|exists:roles,id',
                'id_lokasi' => 'nullable|exists:gudang_dan_tokos,id',
            ]);

            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);

            return response()->json([
                'status' => true,
                'message' => "Pengguna {$user->nama_user} berhasil ditambahkan!",
                'data' => $user,
            ], 201);
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

    /**
     * Display the specified user.
     */
    public function show(string $id)
    {
        try {
            $user = User::with('role', 'lokasi')->findOrFail($id);
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

    /**
     * Show the form for editing the specified user.
     */
    public function edit(string $id)
    {
        try {
            $user = User::with('role', 'lokasi')->findOrFail($id);
            $roles = Role::all();
            $lokasis = GudangDanToko::all();
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

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = User::findOrFail($id);

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

            $user->update($validated);

            return response()->json([
                'status' => true,
                'message' => "Data pengguna {$user->nama_user} berhasil diperbarui!",
                'data' => $user,
            ]);
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

    /**
     * Deactivate the specified user from storage.
     */
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

            $user->update(['flag' => 0]);

            return response()->json([
                'status' => true,
                'message' => "Pengguna {$user->nama_user} berhasil dinonaktifkan!",
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
                'message' => "Terjadi kesalahan saat menonaktifkan pengguna dengan ID {$id}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Activate the specified user from storage.
     */
    public function activate(string $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->update(['flag' => 1]);

            return response()->json([
                'status' => true,
                'message' => "Pengguna {$user->nama_user} berhasil diaktifkan!",
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
                'message' => "Terjadi kesalahan saat mengaktifkan pengguna dengan ID {$id}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}