<x-default-layout>
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="flex justify-between items-center p-4 border-b">
            <h1 class="text-2xl font-bold text-gray-800">Daftar Supplier</h1>
            <a href="{{ route('suppliers.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-medium px-4 py-2 rounded">
                Tambah Supplier
            </a>
        </div>

        <div class="overflow-x-auto p-4">
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

            <livewire:suppliers-table />
        </div>
    </div>
</x-default-layout>