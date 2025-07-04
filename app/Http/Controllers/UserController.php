<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\GudangDanToko;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserEditResource;
use App\Http\Resources\UserShowResource;
use App\Http\Resources\UserIndexResource;
use App\Http\Resources\RoleCreateResource;
use App\Http\Resources\LokasiCreateResource;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\UserIndexDashboardResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $users = User::with([
                'role:id,nama_role', 
            ])
            ->orderBy('id')
            ->get([
                'id', 'nama_user', 'email', 'id_role'
            ]);

            $headings = [
                "NO",
                "Nama User",
                "Email",
                "Role",
            ];

            return response()->json([
                'status' => true,
                'message' => 'Data Pengguna',
                'data' => [
                    'users' => UserIndexDashboardResource::collection($users),

                    /** @var array<int, string> */
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
            $lokasi = GudangDanToko::select(['id', 'nama_gudang_toko'])
                ->where('kategori_bangunan', '=', 0)
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Data untuk Form Tambah Pengguna',
                'data' => [
                    'roles' => RoleCreateResource::collection($roles),
                    'lokasi' => LokasiCreateResource::collection($lokasi),
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

            $validated['password'] = Hash::make($validated['password']);

            DB::transaction(function () use ($validated) {
                User::create($validated);
            }, 3);

            return response()->json([
                'status' => true,
                'message' => "Pengguna baru berhasil ditambahkan!",
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

    public function show(string $id)
    {
        try {
            if (auth()->user()->id != 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized Access',
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
                'message' => "Detail Data Pengguna : {$user->nama_user}",
                'data' => new UserShowResource($user),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pengguna dengan ID yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengambil detail data pengguna",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(string $id)
    {
        try {
            if (auth()->user()->id != 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized Access',
                ], 403);
            }

            $user = User::with([
                'role:id,nama_role', 'lokasi:id,nama_gudang_toko'
            ])->findOrFail($id, [
                'id', 'nama_user', 'email', 'id_role', 'id_lokasi', 'flag'
            ]);
            $roles = Role::select(['id', 'nama_role'])->get();
            $lokasis = GudangDanToko::select(['id', 'nama_gudang_toko'])
                ->where('kategori_bangunan', '=', 0)
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Data untuk Form Edit Pengguna',
                'data' => [
                    'user' => new UserEditResource($user),
                    'roles' => RoleCreateResource::collection($roles),
                    'lokasis' => LokasiCreateResource::collection($lokasis),
                ],
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pengguna yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
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
            if (auth()->user()->id != 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized Access',
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
                if ($request->has('reset_password')) {
                    $rules['password'] = 'string|min:8';
                } else {
                    $rules['password'] = 'string|min:8|confirmed';
                }
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
                'message' => 'Data yang diberikan tidak valid.',
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

    public function deactivate(string $id)
    {
        try {
            if (auth()->user()->id != 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized Access',
                ], 403);
            }

            $user = User::findOrFail($id, [
                'id', 'nama_user', 'email', 'id_role', 'id_lokasi', 'flag'
            ]);

            if ($user->id === Auth::id()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Anda tidak dapat menonaktifkan akun yang sedang digunakan.',
                ], 403); // Forbidden
            }

            if ($user->flag == 0) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Pengguna : {$user->nama_user} sudah dinonaktifkan sebelumnya.",
                ], 409);
            }

            DB::transaction(function () use ($user) {
                $user->update(['flag' => 0]);
            }, 3);

            return response()->json([
                'status' => true,
                'message' => "Pengguna {$user->nama_user} berhasil dinonaktifkan!",
                'data' => new UserIndexResource($user),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pengguna yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat menonaktifkan pengguna",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function activate(string $id)
    {
        try {
            if (auth()->user()->id != 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized Access',
                ], 403);
            }
            
            $user = User::findOrFail($id);

            if ($user->flag == 1) {
                return response()->json([
                    'status' => false,
                    'message' => "Pengguna {$user->nama_user} sudah diaktifkan sebelumnya.",
                ], 409);
            }

            DB::transaction(function () use ($user) {
                // Set flag to 1 to activate the user
                $user->update(['flag' => 1]);
            }, 3);
            
            return response()->json([
                'status' => true,
                'message' => "Pengguna {$user->nama_user} berhasil diaktifkan!",
                'data' => new UserIndexResource($user),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => "Data Pengguna yang dicari tidak ditemukan.",
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Terjadi kesalahan saat mengaktifkan pengguna",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}