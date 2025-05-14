<x-default-layout>
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Detail Kategori Barang</h1>
            <a href="{{ route('categories.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
            </a>
        </div>

    
        <div class="bg-gray-50 p-4 rounded-md mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">ID Kategori</h3>
                    <p class="mt-1 text-lg">{{ $category->id }}</p>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Nama Kategori</h3>
                    <p class="mt-1 text-lg font-medium">{{ $category->nama_kategori_barang }}</p>
                </div>
            </div>
        </div>
    </div>
</x-default-layout>