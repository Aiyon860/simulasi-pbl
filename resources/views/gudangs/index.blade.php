<x-default-layout>
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Daftar Gudang</h1>
            @if(auth()->check() && auth()->user()->hasRole('SuperAdmin'))
                <a href="{{ route('gudangs.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded transition duration-300">
                    Tambah Gudang Baru
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

        @if ($gudangs->count() > 0)
            <div class="overflow-x-auto">
                <livewire:gudangs-table />
            </div>
            <div class="mt-4">
                {{ $gudangs->links() }}
            </div>
        @else
            <div class="bg-yellow-100 rounded-lg p-4 text-yellow-700">
                Belum ada data gudang. Silakan tambahkan gudang baru.
            </div>
        @endif
    </div>
</x-default-layout>