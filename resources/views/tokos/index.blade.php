<x-default-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex flex-col gap-6">
                <div class="flex justify-between items-center gap-12">
                    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                        Daftar Toko
                    </h2>
                    @if(auth()->check() && auth()->user()->hasRole('SuperAdmin'))
                        <a href="{{ route('tokos.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Tambah Toko
                        </a>
                    @endif
                </div>

                <div class="bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <livewire:tokos-table />
                    </div>
                    
                    <div class="mt-4">
                        {{ $tokos->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-default-layout>