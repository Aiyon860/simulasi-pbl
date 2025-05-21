<x-default-layout>
    <div class="bg-white shadow overflow-hidden sm:rounded-lg m-6">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">
                    Daftar Barang
                </h2>
            </div>
            @if(auth()->check() && auth()->user()->hasRole('SuperAdmin'))
                <div>
                    <a href="{{ route('barangs.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Tambah Barang
                    </a>
                </div>
            @endif
        </div>

        <div class="px-4 py-5 sm:px-6">
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
            
            <div class="border-t border-gray-200">
                <div class="overflow-x-auto">
                    <livewire:barangs-table />
                </div>
                <div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
                    {{ $barangs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-default-layout>