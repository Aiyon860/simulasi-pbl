@section('page-title', 'Manajemen Barang')
@section('page-subtitle', 'Kategori Barang')
<x-default-layout>
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6 gap-12">
            <h1 class="text-2xl font-bold text-gray-800">Daftar Kategori Barang</h1>
            @if(auth()->check() && auth()->user()->hasRole('SuperAdmin'))
                <a href="{{ route('categories.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md">
                    Tambah Kategori
                </a>
            @endif
        </div>

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
        @endif
        
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
    
        <livewire:kategori-barang-table/>

        @if($categories->isEmpty())
        <div class="text-center py-8">
            <p class="text-gray-500">Tidak ada data kategori barang.</p>
        </div>
        @endif
    </div>
</x-default-layout>