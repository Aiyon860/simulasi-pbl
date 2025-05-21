<div class="flex items-center space-x-2">
  <!-- View -->
  <a
    href="{{ $rute_lihat }}"
    aria-label="Lihat"
    class="bg-gray-500 hover:bg-gray-600 text-white focus:ring-4 focus:outline-none focus:ring-gray-300 rounded-lg p-2 inline-flex items-center"
  >
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
      <path stroke-linecap="round" stroke-linejoin="round"
            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
    </svg>
  </a>

  <!-- Edit -->
  <a 
    href="{{ $rute_edit }}"
    aria-label="Ubah"
    class="bg-gray-800 hover:bg-gray-900 text-white focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg p-2 inline-flex items-center"
  >
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
      <path stroke-linecap="round" stroke-linejoin="round"
            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
    </svg>
  </a>

  <!-- Delete -->
  <form 
    action="{{ $row->flag == 0 ? route('kategori-barangs.activate', $row->id) : route('kategori-barangs.deactivate', $row->id) }}" 
    method="post"
    onsubmit="return confirm('Yakin ingin mengubah status kategori ini?');"
>
    @csrf
    @method('PATCH')

    <form 
    action="{{ $row->flag == 0 ? route('kategori-barangs.activate', $row->id) : route('kategori-barangs.deactivate', $row->id) }}" 
    method="post"
    onsubmit="return confirm('Yakin ingin mengubah status kategori ini?');"
>
    @csrf
    @method('PATCH')

    <button type="submit" aria-label="Toggle Status" class="relative inline-flex items-center h-8 w-16 rounded-full transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 {{ $row->flag == 1 ? 'bg-green-600' : 'bg-red-300' }}">
        <span class="sr-only">Toggle Status</span>

        {{-- Lingkaran --}}
        <span class="inline-block w-6 h-6 transform rounded-full bg-white shadow-md transition duration-300 ease-in-out 
            {{ $row->flag == 1 ? 'translate-x-8' : 'translate-x-1' }}">
            @if ($row->flag == 1)
                {{-- Centang --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-600 p-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            @else
                {{-- Silang --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-600 p-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            @endif
        </span>
    </button>
</form>

</form>


</div>
