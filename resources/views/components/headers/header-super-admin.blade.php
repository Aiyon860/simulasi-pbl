<button data-drawer-target="sidebar-multi-level-sidebar" data-drawer-toggle="sidebar-multi-level-sidebar" aria-controls="sidebar-multi-level-sidebar" type="button"
    class="absolute top-2 left-2 p-2 text-sm text-gray-500 rounded-lg md:hidden focus:outline-none focus:ring-4 focus:ring-gray-200  hover:bg-gray-200">
    <span class="sr-only">Open sidebar</span>
    <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"
        xmlns="http://www.w3.org/2000/svg">
        <path clip-rule="evenodd" fill-rule="evenodd"
            d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z">
        </path>
    </svg>
</button>

<aside id="sidebar-multi-level-sidebar"
  class="fixed top-0 left-0 z-40 w-64 shrink-0 h-screen transition-transform -translate-x-full lg:translate-x-0 lg:-translate-x-full"
  aria-label="Sidebar">
    <div class="ml-5 h-[95%] px-3 py-4 overflow-y-auto bg-[#161A30] rounded-xl shadow-lg mx-auto my-6 w-[95%] flex flex-col">
        <div class="flex items-center mb-4">
            <svg width="41" height="40" viewBox="0 0 41 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M25.529 32.7902L8.708 39.9998L24.5555 22.9637L40.2407 20.1333L25.529 32.7902Z" fill="url(#paint0_linear_4719_3880)"/>
                <path d="M26.0159 5.23365L8.00494 0L24.5555 22.964L40.2407 20.1335L26.0159 5.23365Z" fill="url(#paint1_linear_4719_3880)"/>
                <path d="M24.6095 22.9105L8.70799 40L0 19.5995L8.00486 0L24.6095 22.9105Z" fill="url(#paint2_radial_4719_3880)"/>
                <defs>
                <linearGradient id="paint0_linear_4719_3880" x1="24.4473" y1="23.2842" x2="27.0897" y2="31.9013" gradientUnits="userSpaceOnUse">
                <stop stop-color="#31304D"/>
                <stop offset="0.411667" stop-color="#68697E"/>
                <stop offset="1" stop-color="#B6BBC4"/>
                </linearGradient>
                <linearGradient id="paint1_linear_4719_3880" x1="18.8223" y1="-4.53939" x2="29.0842" y2="22.9966" gradientUnits="userSpaceOnUse">
                <stop stop-color="#31304D"/>
                <stop offset="1" stop-color="#B6BBC4"/>
                </linearGradient>
                <radialGradient id="paint2_radial_4719_3880" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(8.81616 22.3231) rotate(97.3134) scale(19.5449 12.0199)">
                <stop stop-color="#B6BBC4"/>
                <stop offset="1" stop-color="#31304D"/>
                </radialGradient>
                </defs>
                </svg>
                 <!-- Ganti dengan path logo Anda -->
            <span class="text-white text-lg font-semibold pl-1">Gudangku</span> <!-- Judul -->
        </div>
       <ul class="pl-2 flex-grow overflow-y-auto">
          <li>
            <a href="{{ route('dashboard') }}" class="flex items-center p-2 rounded-lg text-white  hover:bg-gray-700 group">
               <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M4 2C2.89543 2 2 2.89543 2 4V8C2 9.10457 2.89543 10 4 10H8C9.10457 10 10 9.10457 10 8V4C10 2.89543 9.10457 2 8 2H4ZM18 10C20.2091 10 22 8.20914 22 6C22 3.79086 20.2091 2 18 2C15.7909 2 14 3.79086 14 6C14 8.20914 15.7909 10 18 10ZM10 18C10 20.2091 8.20914 22 6 22C3.79086 22 2 20.2091 2 18C2 15.7909 3.79086 14 6 14C8.20914 14 10 15.7909 10 18ZM16 14C14.8954 14 14 14.8954 14 16V20C14 21.1046 14.8954 22 16 22H20C21.1046 22 22 21.1046 22 20V16C22 14.8954 21.1046 14 20 14H16Z" fill="#F0ECE5"/>
               </svg>
               <span class="ms-3">Dashboard</span>
            </a>
          </li>
          <li>
             <button type="button" class="flex items-center w-full p-2 text-base transition duration-75 rounded-lg group  text-white hover:bg-gray-700" aria-controls="dropdown-manajemen-barang" data-collapse-toggle="dropdown-manajemen-barang">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M18.3329 4.99259L12.9176 2.2198C12.3453 1.92674 11.6669 1.92673 11.0946 2.21979L5.67937 4.99255L12.0062 8.23207L18.3329 4.99259ZM4.00098 11.6692V16.9253C4.00098 17.6759 4.4213 18.3633 5.08945 18.7055L11.0941 21.78C11.6664 22.0731 12.3448 22.0731 12.9171 21.78L18.9218 18.7055C19.5899 18.3633 20.0103 17.6759 20.0103 16.9253V11.6695L14.6783 14.3996C14.0013 14.7463 13.1782 14.5224 12.7583 13.8775L12.0059 12.7218L11.2536 13.8775C10.8337 14.5224 10.0106 14.7463 9.33357 14.3996L4.00098 11.6692ZM11.9816 9.96677L4.0169 5.87609L2.02881 8.90581L10.0359 13.0274L11.9816 9.96677ZM11.9816 9.96677L14.0048 13.0274L22.0288 8.96485L20.0014 5.90286L11.9816 9.96677Z" fill="#F0ECE5"/>
                    </svg>
                    
                   <span class="flex-1 ms-3 text-left rtl:text-right break-words mr-5">Manajemen Barang</span>
                   <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                   </svg>
             </button>
             <ul id="dropdown-manajemen-barang" class="hidden py-2 space-y-2">
                   <li>
                      <a href="{{ route('categories.index') }}" class="flex items-center w-full p-2 transition duration-75 rounded-lg pl-11 group  text-white hover:bg-gray-700">Kategori Barang</a>
                   </li>
                   <li>
                      <a href="{{ route('barangs.index') }}" class="flex items-center w-full p-2 transition duration-75 rounded-lg pl-11 group  text-white hover:bg-gray-700">Stok Barang</a>
                   </li>
             </ul>
          </li>
          <li>
             <button type="button" class="flex items-center w-full p-2 text-base transition duration-75 rounded-lg group  text-white hover:bg-gray-700" aria-controls="dropdown-aktivitas-gudang" data-collapse-toggle="dropdown-aktivitas-gudang">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_4719_5289)">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M2 1.25C1.58579 1.25 1.25 1.58579 1.25 2C1.25 2.41421 1.58579 2.75 2 2.75H3C3.69036 2.75 4.25 3.30964 4.25 4V18.1454C3.51704 18.4421 3 19.1607 3 20C3 21.1046 3.89543 22 5 22C5.83934 22 6.55793 21.483 6.85462 20.75H22C22.4142 20.75 22.75 20.4142 22.75 20C22.75 19.5858 22.4142 19.25 22 19.25H6.85462C6.65168 18.7486 6.25135 18.3483 5.75 18.1454V4C5.75 2.48122 4.51878 1.25 3 1.25H2ZM8 7C8 5.89543 8.89543 5 10 5H18C19.1046 5 20 5.89543 20 7V15C20 16.1046 19.1046 17 18 17H10C8.89543 17 8 16.1046 8 15V7ZM12.25 8C12.25 7.58579 12.5858 7.25 13 7.25H15C15.4142 7.25 15.75 7.58579 15.75 8C15.75 8.41421 15.4142 8.75 15 8.75H13C12.5858 8.75 12.25 8.41421 12.25 8Z" fill="#F0ECE5"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_4719_5289">
                    <rect width="24" height="24" fill="white"/>
                    </clipPath>
                    </defs>
                    </svg>
                    
                   <span class="flex-1 ms-3 text-left rtl:text-right break-words mr-2">Aktivitas Gudang</span>
                   <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                   </svg>
             </button>
             <ul id="dropdown-aktivitas-gudang" class="hidden py-2 space-y-2">
                   <li>
                      <a href="#penerimaan-barang" class="flex items-center w-full p-2 transition duration-75 rounded-lg pl-11 group  text-white hover:bg-gray-700">Penerimaan Barang</a>
                   </li>
                   <li>
                      <a href="#pengiriman-barang" class="flex items-center w-full p-2 transition duration-75 rounded-lg pl-11 group  text-white hover:bg-gray-700">Pengiriman Barang</a>
                   </li>
                   <li>
                      <a href="#retur-barang" class="flex items-center w-full p-2 transition duration-75 rounded-lg pl-11 group  text-white hover:bg-gray-700">Retur Barang</a>
                   </li>
             </ul>
          </li>
          <li>
             <a href="{{ route('gudangs.index') }}" class="flex items-center p-2 rounded-lg text-white  hover:bg-gray-700 group">
                <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_4719_5450)">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.70615 6.8054C2.63684 7.55391 2 8.77707 2 10.0823V19.9997C2 21.1043 2.89543 21.9997 4 21.9997H5.25V18.9997V14.9997V12.9997C5.25 11.4809 6.48122 10.2497 8 10.2497H16C17.5188 10.2497 18.75 11.4809 18.75 12.9997V14.9997V18.9997V21.9997H20C21.1046 21.9997 22 21.1043 22 19.9997V10.0823C22 8.77707 21.3632 7.55391 20.2938 6.80539L14.2938 2.6054C12.9166 1.6413 11.0834 1.6413 9.70615 2.6054L3.70615 6.8054ZM17.25 21.9997V19.7497H6.75V21.9997H17.25ZM6.75 18.2497V15.7497H17.25V18.2497H6.75ZM6.75 14.2497H17.25V12.9997C17.25 12.3093 16.6904 11.7497 16 11.7497H8C7.30964 11.7497 6.75 12.3093 6.75 12.9997V14.2497Z" fill="#F0ECE5"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_4719_5450">
                    <rect width="24" height="24" fill="white"/>
                    </clipPath>
                    </defs>
                    </svg>
                    
                <span class="flex-1 ms-3 mr-5">Manajemen Gudang</span>                
             </a>
          </li>
          <li>
             <a href="{{ route('tokos.index') }}" class="flex items-center p-2 rounded-lg text-white  hover:bg-gray-700 group">
               <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M7.23146 2H16.7689C18.5863 2 20.2206 3.24475 20.8955 5.14305L21.6535 7.27487C21.8829 7.92019 22.0415 8.61074 21.8742 9.27488C21.4778 10.8478 20.192 12 18.6669 12C16.8259 12 15.3335 10.3211 15.3335 8.25C15.3335 10.3211 13.8412 12 12.0002 12C10.1593 12 8.66687 10.3211 8.66687 8.25C8.66687 10.3211 7.17449 12 5.33354 12C3.80844 12 2.52257 10.8478 2.12622 9.27488C1.95887 8.61074 2.11747 7.92019 2.34692 7.27487L3.1049 5.14305C3.77985 3.24476 5.41411 2 7.23146 2ZM4.00021 13.3005V17.9999C4.00021 20.2091 5.79107 21.9999 8.00021 21.9999H9.00021V20C9.00021 18.3431 10.3434 17 12.0002 17C13.6571 17 15.0002 18.3431 15.0002 20V21.9999H16.0002C18.2093 21.9999 20.0002 20.2091 20.0002 17.9999V13.3005C19.5814 13.4298 19.135 13.4999 18.6669 13.4999C17.3298 13.4999 16.1774 12.9373 15.3336 12.0559C14.4898 12.9373 13.3374 13.4999 12.0003 13.4999C10.6632 13.4999 9.51076 12.9373 8.66695 12.0559C7.82313 12.9373 6.67073 13.4999 5.33361 13.4999C4.86548 13.4999 4.41907 13.4297 4.00021 13.3005Z" fill="#F0ECE5"/>
                  </svg>                    
                <span class="flex-1 ms-3 mr-5">Manajemen Pelanggan</span>                
             </a>
          </li>
          <li>
             <a href="{{ route('suppliers.index') }}" class="flex items-center p-2 rounded-lg text-white  hover:bg-gray-700 group">
                <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 3.06201H9.5C11.7091 3.06201 13.5 4.85287 13.5 7.06201V19.062H10.5C10.5 17.129 8.933 15.562 7 15.562C5.34384 15.562 3.95711 16.7117 3.59319 18.2572C2.62551 17.5271 2 16.3676 2 15.062V7.06201C2 4.85287 3.79086 3.06201 6 3.06201ZM9 19.062C9 20.1666 8.10457 21.062 7 21.062C5.89543 21.062 5 20.1666 5 19.062C5 19.02 5.00129 18.9783 5.00384 18.937C5.06838 17.8907 5.93742 17.062 7 17.062C8.10457 17.062 9 17.9574 9 19.062ZM20 19.062C20 20.1666 19.1046 21.062 18 21.062C16.8954 21.062 16 20.1666 16 19.062C16 17.9574 16.8954 17.062 18 17.062C19.1046 17.062 20 17.9574 20 19.062ZM18 15.562C19.7222 15.562 21.1538 16.8058 21.4456 18.4441C21.7891 18.085 22 17.5981 22 17.062V11.6785C22 11.1257 21.7712 10.5975 21.3679 10.2194L18.577 7.60294C18.2063 7.25541 17.7172 7.06201 17.2091 7.06201H15V17.2582C15.6124 16.2418 16.7268 15.562 18 15.562Z" fill="#F0ECE5"/>
                    </svg>
                    
                <span class="flex-1 ms-3 mr-5">Manajemen Supplier</span>                
             </a>
          </li>
          <li>
             <a href="{{ route('users.index') }}" class="flex items-center p-2 rounded-lg text-white  hover:bg-gray-700 group">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11ZM12 21C15.866 21 19 19.2091 19 17C19 14.7909 15.866 13 12 13C8.13401 13 5 14.7909 5 17C5 19.2091 8.13401 21 12 21Z" fill="#F0ECE5"/>
                    </svg>
                    
                <span class="flex-1 ms-3 whitespace-nowrap">Manajemen User</span>
             </a>
          </li>
       </ul>
       <x-footer />
    </div>
 </aside>
 
 